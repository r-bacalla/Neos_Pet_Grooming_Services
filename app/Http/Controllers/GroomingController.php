<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\AuditLog;
use App\Models\GroomingAppointment;
use App\Models\GroomingAddon;
use App\Models\GroomingVisitGroup;
use App\Models\LoyaltyVoucher;
use App\Models\Pet;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GroomingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->isCustomer()) {
            return redirect()->route('grooming.create');
        }

        $search = trim((string) $request->query('search', ''));
        $statusFilter = $request->query('status', 'all');

        $appointments = GroomingAppointment::with([
            'customer',
            'pet',
            'sale',
            'service',
            'groomer',
        ])
            ->where('status', '!=', 'cancelled')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('pet', fn ($petQuery) => $petQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($statusFilter === 'pending', fn ($query) => $query->where('status', 'pending'))
            ->when($statusFilter === 'in_progress', fn ($query) => $query->whereIn('status', ['approved', 'received', 'in_progress']))
            ->when($statusFilter === 'completed', fn ($query) => $query->whereIn('status', ['ready', 'completed'])->whereNotNull('sale_id'))
            ->when($statusFilter === 'unpaid', fn ($query) => $query->whereIn('status', ['ready', 'completed'])->whereNull('sale_id'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $groomingAddons = GroomingAddon::where('active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $allGroomingAddons = GroomingAddon::withTrashed()->get()->keyBy('id');
        $visitGroupIds = $appointments->getCollection()->pluck('visit_group_id')->filter()->unique();
        $visitGroups = $visitGroupIds->isEmpty()
            ? collect()
            : GroomingAppointment::with(['service', 'pet'])
                ->whereIn('visit_group_id', $visitGroupIds)
                ->get()
                ->groupBy('visit_group_id');
        $visitAddonEditData = [];

        foreach ($appointments as $appointment) {
            $group = $appointment->visit_group_id
                ? $visitGroups->get($appointment->visit_group_id, collect())
                : collect([$appointment]);
            $existingAddonIds = $group->flatMap(fn ($member) => $member->add_on_ids ?? [])
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values();
            $inactiveAddons = $existingAddonIds
                ->filter(fn ($id) => !$groomingAddons->contains('id', (int) $id))
                ->map(function ($id) use ($allGroomingAddons) {
                    $addon = $allGroomingAddons->get((int) $id);

                    return [
                        'id' => (string) $id,
                        'name' => $addon?->name ?? 'Archived add-on',
                        'category' => $addon?->category ?? 'extra_service',
                        'rate' => (float) ($addon?->rate ?? 0),
                    ];
                })
                ->values();
            $existingAddonTotal = (float) $existingAddonIds->sum(
                fn ($id) => (float) ($allGroomingAddons->get((int) $id)?->rate ?? 0)
            );
            $baseTotal = (float) $group->sum(fn ($member) => $member->service
                ? (float) $member->service->rate
                : max(0, (float) $member->grooming_price - $existingAddonTotal));

            $visitAddonEditData[$appointment->id] = [
                'appointment_id' => $appointment->id,
                'pet_name' => $appointment->pet?->name ?? 'Pet',
                'group_size' => max($group->count(), 1),
                'base_total' => $baseTotal,
                'paid' => $group->every(fn ($member) => $member->sale_id !== null),
                'current_add_on_ids' => $existingAddonIds->all(),
                'inactive_addons' => $inactiveAddons->all(),
            ];
        }

        return view('grooming.index', compact('appointments', 'groomingAddons', 'visitAddonEditData'));
    }

    public function updateAddons(Request $request, GroomingAppointment $appointment)
    {
        $validated = $request->validate([
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['integer', 'distinct', 'exists:grooming_addons,id'],
            'payment_method' => ['nullable', 'in:cash,card,gcash,bank_transfer'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $selectedAddonIds = collect($validated['add_on_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $result = DB::transaction(function () use ($request, $appointment, $selectedAddonIds, $validated) {
            $lockedAppointment = GroomingAppointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);
            $appointments = $lockedAppointment->visit_group_id
                ? GroomingAppointment::with(['pet', 'service'])
                    ->where('visit_group_id', $lockedAppointment->visit_group_id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                : GroomingAppointment::with(['pet', 'service'])
                    ->whereKey($lockedAppointment->id)
                    ->lockForUpdate()
                    ->get();

            if ($appointments->isEmpty() || $appointments->contains(fn ($member) => $member->status === 'cancelled')) {
                throw ValidationException::withMessages(['add_on_ids' => 'Cancelled grooming visits cannot be edited.']);
            }

            $paidCount = $appointments->filter(fn ($member) => $member->sale_id !== null)->count();
            if ($paidCount !== 0 && $paidCount !== $appointments->count()) {
                throw ValidationException::withMessages(['add_on_ids' => 'This visit has inconsistent payment records and cannot be edited.']);
            }
            $isPaid = $paidCount === $appointments->count();

            $existingAddonIds = $appointments->flatMap(fn ($member) => $member->add_on_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
            $newAddonIds = $selectedAddonIds->diff($existingAddonIds)->values();

            if ($isPaid && $existingAddonIds->diff($selectedAddonIds)->isNotEmpty()) {
                throw ValidationException::withMessages(['add_on_ids' => 'Previously paid add-ons cannot be removed from a completed sale.']);
            }

            $selectedAddons = GroomingAddon::withTrashed()
                ->whereIn('id', $selectedAddonIds)
                ->get()
                ->keyBy('id');
            if ($selectedAddons->count() !== $selectedAddonIds->count()) {
                throw ValidationException::withMessages(['add_on_ids' => 'One or more selected add-ons no longer exist.']);
            }

            $newAddons = GroomingAddon::where('active', true)
                ->whereIn('id', $newAddonIds)
                ->get()
                ->keyBy('id');
            if ($newAddons->count() !== $newAddonIds->count()) {
                throw ValidationException::withMessages(['add_on_ids' => 'New add-ons must be active.']);
            }

            if ($selectedAddons->where('category', 'detangling')->count() > 1) {
                throw ValidationException::withMessages(['add_on_ids' => 'Choose only one detangling add-on.']);
            }

            if ($isPaid && $newAddonIds->isEmpty()) {
                throw ValidationException::withMessages(['add_on_ids' => 'Select at least one new add-on to create an additional charge.']);
            }

            $paymentSale = null;
            if ($isPaid) {
                $paymentAddons = $newAddons;
                $amountDue = round((float) $paymentAddons->sum('rate') * $appointments->count(), 2);
                $amountPaid = round((float) ($validated['amount_paid'] ?? 0), 2);

                if (empty($validated['payment_method'])) {
                    throw ValidationException::withMessages(['payment_method' => 'Choose a payment method for the additional add-on charge.']);
                }
                if ($amountPaid < $amountDue) {
                    throw ValidationException::withMessages(['amount_paid' => 'Payment is insufficient for the selected add-ons.']);
                }

                $paymentSale = Sale::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'customer_id' => $lockedAppointment->customer_id,
                    'user_id' => $request->user()->id,
                    'subtotal' => $amountDue,
                    'discount' => 0,
                    'vat_rate' => 0,
                    'vat_amount' => 0,
                    'total' => $amountDue,
                    'amount_paid' => $amountPaid,
                    'change_amount' => round($amountPaid - $amountDue, 2),
                    'payment_method' => $validated['payment_method'],
                    'status' => 'completed',
                    'notes' => 'Additional grooming add-ons for visit ' . ($lockedAppointment->visit_group_id ?? $lockedAppointment->id),
                ]);

                foreach ($appointments as $member) {
                    foreach ($paymentAddons as $addon) {
                        SaleItem::create([
                            'sale_id' => $paymentSale->id,
                            'item_type' => 'service',
                            'item_name' => 'Grooming add-on - ' . $addon->name . ' (' . ($member->pet?->name ?? 'Pet') . ')',
                            'unit_price' => $addon->rate,
                            'quantity' => 1,
                            'discount' => 0,
                            'line_total' => $addon->rate,
                        ]);
                    }
                }
            }

            $selectedAddonTotal = (float) $selectedAddons->sum('rate');
            foreach ($appointments as $member) {
                $basePrice = $member->service
                    ? (float) $member->service->rate
                    : max(0, (float) $member->grooming_price - (float) $existingAddonIds->sum(
                        fn ($id) => (float) $selectedAddons->get($id)?->rate
                    ));
                $member->update([
                    'add_on_ids' => $selectedAddonIds->all(),
                    'grooming_price' => round($basePrice + $selectedAddonTotal, 2),
                ]);
            }

            $this->recordGroomingActivity(
                $lockedAppointment,
                $isPaid ? 'grooming_addons_charged' : 'grooming_addons_updated',
                $isPaid ? 'Collected an additional grooming add-on payment.' : 'Updated grooming add-ons and visit balance.',
                ['add_on_ids' => $existingAddonIds->all()],
                ['add_on_ids' => $selectedAddonIds->all(), 'additional_sale_id' => $paymentSale?->id]
            );

            return $paymentSale;
        });

        if ($result) {
            return redirect()->route('receipts.done', $result)
                ->with('success', 'Additional grooming add-ons paid successfully.');
        }

        return redirect()->route('grooming.index')
            ->with('success', 'Grooming add-ons updated. The new total is included in the outstanding balance.');
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $customerAccount = null;

        if ($user->isCustomer()) {
            $customerAccount = Customer::with('pets')
                ->where('email', $user->email)
                ->orderBy('id')
                ->first();
            $customers = $customerAccount ? collect([$customerAccount]) : collect();
            $queue = collect();
        } else {
            $customers = Customer::with('pets')
                ->orderBy('name')
                ->get();
            $queue = GroomingAppointment::with(['customer', 'pet', 'service', 'groomer'])
                ->whereIn('status', ['pending', 'approved', 'received', 'in_progress', 'ready'])
                ->latest()
                ->limit(7)
                ->get();
        }

        $services = Service::where('active', true)
            ->orderBy('name')
            ->get();
        $groomingAddons = GroomingAddon::where('active', true)
            ->orderBy('category')
            ->orderBy('rate')
            ->orderBy('name')
            ->get();

        return view('grooming.wizard', compact('customers', 'services', 'groomingAddons', 'queue', 'customerAccount'));
    }

    public function payment(Request $request, GroomingAppointment $appointment)
    {
        if ($request->user()->isCustomer()) {
            $customerId = Customer::query()
                ->where('email', $request->user()->email)
                ->value('id');

            abort_unless((int) $customerId === (int) $appointment->customer_id, 403);
        }

        $appointments = GroomingAppointment::query()
            ->with(['customer', 'pet', 'service'])
            ->when(
                $appointment->visit_group_id,
                fn ($query) => $query->where('visit_group_id', $appointment->visit_group_id),
                fn ($query) => $query->whereKey($appointment->id)
            )
            ->orderBy('id')
            ->get();

        abort_unless($appointments->isNotEmpty() && $appointments->every(
            fn ($visitAppointment) =>
                in_array($visitAppointment->status, ['pending', 'approved', 'received', 'in_progress', 'ready', 'completed'], true) &&
                !$visitAppointment->sale_id &&
                (float) $visitAppointment->grooming_price > 0
        ), 404);

        $groomingSubtotal = round((float) $appointments->sum('grooming_price'), 2);
        $addonCounts = [];
        foreach ($appointments as $visitAppointment) {
            foreach ($visitAppointment->add_on_ids ?? [] as $addonId) {
                $addonId = (int) $addonId;
                $addonCounts[$addonId] = ($addonCounts[$addonId] ?? 0) + 1;
            }
        }
        $addonLines = GroomingAddon::query()
            ->whereIn('id', array_keys($addonCounts))
            ->get()
            ->keyBy('id');
        $addonLines = collect($addonCounts)->map(function ($quantity, $addonId) use ($addonLines) {
            $addon = $addonLines->get($addonId);

            return $addon ? [
                'name' => $addon->name,
                'quantity' => $quantity,
                'total' => (float) $addon->rate * $quantity,
            ] : null;
        })->filter()->values();
        $subtotal = $groomingSubtotal;
        $voucher = LoyaltyVoucher::query()
            ->where('customer_id', $appointment->customer_id)
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', today())
            ->orderBy('expires_at')
            ->first();
        $discount = min(
            $subtotal,
            $voucher ? round($groomingSubtotal * ((float) $voucher->discount_percent / 100), 2) : 0
        );
        $total = round(max(0, $subtotal - $discount), 2);

        return view('grooming.payment', compact('appointment', 'appointments', 'voucher', 'subtotal', 'discount', 'total', 'addonLines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'pet_id' => ['nullable', 'integer', 'exists:pets,id'],
            'pet_name' => ['required', 'string', 'max:255'],
            'species' => ['required', 'in:Dog,Cat,Other'],
            'breed' => ['nullable', 'string', 'max:255'],
            'weight_category' => ['nullable', 'required_if:species,Dog', 'in:Small,Medium,Large,XLarge,Any'],
            'profile_size' => ['nullable', 'required_if:species,Other', 'in:Small,Medium,Large,XLarge'],
            'update_saved_profile_size' => ['nullable', 'boolean'],
            'sex' => ['nullable', 'in:Male,Female,Unknown'],
            'additional_pets' => ['nullable', 'array', 'max:10'],
            'additional_pets.*.pet_id' => ['nullable', 'integer', 'exists:pets,id'],
            'additional_pets.*.name' => ['required', 'string', 'max:255'],
            'additional_pets.*.species' => ['required', 'in:Dog,Cat,Other'],
            'additional_pets.*.breed' => ['nullable', 'string', 'max:255'],
            'additional_pets.*.weight_category' => ['nullable', 'required_if:additional_pets.*.species,Dog', 'in:Small,Medium,Large,XLarge,Any'],
            'additional_pets.*.profile_size' => ['nullable', 'required_if:additional_pets.*.species,Other', 'in:Small,Medium,Large,XLarge'],
            'additional_pets.*.update_saved_profile_size' => ['nullable', 'boolean'],
            'additional_pets.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'additional_pets.*.add_on_ids' => ['nullable', 'array'],
            'additional_pets.*.add_on_ids.*' => ['integer', 'distinct', 'exists:grooming_addons,id'],
            'additional_pets.*.detangling_addon_id' => ['nullable', 'integer', 'exists:grooming_addons,id'],
            'additional_pets.*.sex' => ['nullable', 'in:Male,Female,Unknown'],
            'waiver_confirmed' => ['accepted'],
            'waiver_photo' => ['nullable', 'image', 'max:10240'],
            'groomer_name' => ['nullable', 'string', 'max:255'],
            'payment_action' => ['nullable', 'in:pay_now,pay_later'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['integer', 'distinct', 'exists:grooming_addons,id'],
            'detangling_addon_id' => ['nullable', 'integer', 'exists:grooming_addons,id'],
            'groomer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'groomer')),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($request->user()->isCustomer()) {
            $customerAccount = Customer::query()
                ->where('email', $request->user()->email)
                ->orderBy('id')
                ->first();

            $validated['customer_id'] = $customerAccount?->id;
            $validated['customer_name'] = $request->user()->name;
            $validated['customer_email'] = $request->user()->email;
        }

        $validated['weight_category'] = $validated['species'] === 'Dog'
            ? ($validated['weight_category'] ?? null)
            : 'Any';
        $validated['profile_size'] = $validated['species'] === 'Other'
            ? $validated['profile_size']
            : null;

        $service = Service::where('active', true)->findOrFail($validated['service_id']);
        if (!$this->serviceMatchesPet($service, $validated['species'], $validated['weight_category'])) {
            return back()->withInput()->withErrors([
                'service_id' => 'Choose a package variant matching the pet type and size.',
            ]);
        }

        $additionalPets = $validated['additional_pets'] ?? [];
        $additionalServiceVariants = [];
        foreach ($additionalPets as $index => $additionalPet) {
            $additionalPets[$index]['weight_category'] = $additionalPet['species'] === 'Dog'
                ? ($additionalPet['weight_category'] ?? null)
                : 'Any';
            $additionalPets[$index]['profile_size'] = $additionalPet['species'] === 'Other'
                ? $additionalPet['profile_size']
                : null;
            $additionalPet = $additionalPets[$index];
            $additionalService = Service::where('active', true)->find($additionalPet['service_id']);
            if (!$additionalService || !$this->serviceMatchesPet($additionalService, $additionalPet['species'], $additionalPet['weight_category'])) {
                return back()->withInput()->withErrors([
                    "additional_pets.{$index}.service_id" => 'Choose an active package matching this pet type and size.',
                ]);
            }
            $additionalServiceVariants[] = $additionalService;
        }

        $addonRequests = [[
            'service' => $service,
            'extra_ids' => $validated['add_on_ids'] ?? [],
            'detangling_id' => $validated['detangling_addon_id'] ?? null,
            'error_key' => 'add_on_ids',
        ]];
        foreach ($additionalPets as $index => $additionalPet) {
            $addonRequests[] = [
                'service' => $additionalServiceVariants[$index],
                'extra_ids' => $additionalPet['add_on_ids'] ?? [],
                'detangling_id' => $additionalPet['detangling_addon_id'] ?? null,
                'error_key' => "additional_pets.{$index}.add_on_ids",
            ];
        }

        $addonSelections = [];
        foreach ($addonRequests as $selectionIndex => $addonRequest) {
            $requestedExtraIds = array_map('intval', $addonRequest['extra_ids']);
            $detanglingAddonId = filled($addonRequest['detangling_id']) ? (int) $addonRequest['detangling_id'] : null;
            $extraAddons = GroomingAddon::where('active', true)
                ->where('category', 'extra_service')
                ->whereIn('id', $requestedExtraIds)
                ->get();
            $detanglingAddon = $detanglingAddonId
                ? GroomingAddon::where('active', true)->where('category', 'detangling')->find($detanglingAddonId)
                : null;

            if ($extraAddons->count() !== count($requestedExtraIds) || ($detanglingAddonId && !$detanglingAddon)) {
                return back()->withInput()->withErrors([
                    $addonRequest['error_key'] => 'Choose active add-ons in their configured categories.',
                ]);
            }

            $addOns = $extraAddons->when($detanglingAddon, fn ($addons) => $addons->push($detanglingAddon));
            $selectedIds = $addOns->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $allowedIds = $addonRequest['service']->available_add_on_ids;
            if ($allowedIds === null) {
                $allowedIds = [];
            }
            $allowedIds = array_map('intval', $allowedIds);

            if (array_diff($selectedIds, $allowedIds)) {
                return back()->withInput()->withErrors([
                    $addonRequest['error_key'] => 'One or more add-ons are not available for this service.',
                ]);
            }

            $addonSelections[$selectionIndex] = [
                'ids' => $selectedIds,
                'total' => (float) $addOns->sum('rate'),
            ];
        }

        $additionalPetIds = array_values(array_filter(array_map(
            fn ($additionalPet) => (int) ($additionalPet['pet_id'] ?? 0),
            $additionalPets
        )));

        if (
            count($additionalPetIds) !== count(array_unique($additionalPetIds)) ||
            (!empty($validated['pet_id']) && in_array((int) $validated['pet_id'], $additionalPetIds, true))
        ) {
            return back()->withInput()->withErrors([
                'additional_pets' => 'Each pet can only appear once in a grooming visit.',
            ]);
        }

        $waiverPhotoPath = $request->file('waiver_photo')
            ? $request->file('waiver_photo')->store('grooming-waivers', 'local')
            : null;

        $appointment = DB::transaction(function () use ($validated, $additionalPets, $additionalServiceVariants, $service, $addonSelections, $waiverPhotoPath) {
            if (!empty($validated['customer_id'])) {
                $customer = Customer::query()->lockForUpdate()->findOrFail($validated['customer_id']);
                $customer->update([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['customer_address'],
                ]);
            } else {
                $customer = Customer::create([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['customer_address'],
                    'loyalty_visits' => 0,
                ]);
            }

            $petAttributes = [
                'name' => $validated['pet_name'],
                'species' => $validated['species'],
                'breed' => $validated['breed'] ?? null,
                'sex' => $validated['sex'] ?? null,
            ];

            if (!empty($validated['pet_id'])) {
                $pet = $customer->pets()->lockForUpdate()->find($validated['pet_id']);
                if (!$pet) {
                    abort(422, 'The selected pet does not belong to this customer.');
                }
                if (!empty($validated['update_saved_profile_size'])) {
                    $petAttributes['weight_category'] = $validated['species'] === 'Dog'
                        ? $validated['weight_category']
                        : $validated['profile_size'];
                }
                $pet->update($petAttributes);
            } else {
                $petAttributes['weight_category'] = match ($validated['species']) {
                    'Dog' => $validated['weight_category'],
                    'Other' => $validated['profile_size'],
                    default => null,
                };
                $pet = $customer->pets()->create($petAttributes);
            }

            $visitGroup = GroomingVisitGroup::create([
                'customer_id' => $customer->id,
            ]);

            $appointmentAttributes = [
                'customer_id' => $customer->id,
                'visit_group_id' => $visitGroup->id,
                'pet_size' => $validated['species'] === 'Dog' ? $validated['weight_category'] : null,
                'service_id' => $service->id,
                'add_on_ids' => $addonSelections[0]['ids'],
                'groomer_id' => $validated['groomer_id'] ?? null,
                'groomer_name' => filled($validated['groomer_name'] ?? null) ? trim($validated['groomer_name']) : null,
                'sale_id' => null,
                'scheduled_start' => now(),
                'scheduled_end' => null,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'groomer_notes' => null,
                'actual_weight' => null,
                'grooming_price' => (float) $service->rate + $addonSelections[0]['total'],
                'waiver_confirmed_at' => now(),
                'waiver_photo_path' => $waiverPhotoPath,
                'checked_in_at' => now(),
                'approved_at' => null,
                'received_at' => null,
                'started_at' => null,
                'ready_at' => null,
                'completed_at' => null,
            ];

            $appointment = GroomingAppointment::create([
                ...$appointmentAttributes,
                'pet_id' => $pet->id,
            ]);

            foreach ($additionalPets as $index => $additionalPet) {
                $visitPetAttributes = [
                    'name' => $additionalPet['name'],
                    'species' => $additionalPet['species'],
                    'breed' => $additionalPet['breed'] ?? null,
                    'sex' => $additionalPet['sex'] ?? null,
                ];

                if (!empty($additionalPet['pet_id'])) {
                    $visitPet = $customer->pets()->lockForUpdate()->find($additionalPet['pet_id']);
                    if (!$visitPet) {
                        abort(422, 'A selected pet does not belong to this customer.');
                    }
                    if (!empty($additionalPet['update_saved_profile_size'])) {
                        $visitPetAttributes['weight_category'] = $additionalPet['species'] === 'Dog'
                            ? $additionalPet['weight_category']
                            : $additionalPet['profile_size'];
                    }
                    $visitPet->update($visitPetAttributes);
                } else {
                    $visitPetAttributes['weight_category'] = match ($additionalPet['species']) {
                        'Dog' => $additionalPet['weight_category'],
                        'Other' => $additionalPet['profile_size'],
                        default => null,
                    };
                    $visitPet = $customer->pets()->create($visitPetAttributes);
                }

                GroomingAppointment::create([
                    ...$appointmentAttributes,
                    'pet_size' => $additionalPet['species'] === 'Dog' ? $additionalPet['weight_category'] : null,
                    'service_id' => $additionalServiceVariants[$index]->id,
                    'add_on_ids' => $addonSelections[$index + 1]['ids'],
                    'grooming_price' => (float) $additionalServiceVariants[$index]->rate + $addonSelections[$index + 1]['total'],
                    'pet_id' => $visitPet->id,
                ]);
            }

            return $appointment;
        });

        $appointment->load(['customer', 'pet']);
        $this->recordGroomingActivity(
            $appointment,
            'grooming_requested',
            'Created grooming request for ' . $appointment->pet->name . ' for ' . $appointment->customer->name . '.',
            [],
            ['status' => $appointment->status, 'service_id' => $appointment->service_id]
        );

        if (($validated['payment_action'] ?? 'pay_now') === 'pay_later') {
            return redirect()->route('grooming.index')
                ->with('success', 'Grooming request saved. Payment can be collected from the grooming queue.');
        }

        return redirect()->route('grooming.payment', $appointment)
            ->with('success', 'Grooming request saved. Complete payment to finish the visit.');
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

    public function approve(GroomingAppointment $appointment)
    {
        if ($appointment->status !== 'pending') {
            return back()->with(
                'error',
                'Only pending grooming requests can be approved.'
            );
        }

        $previousStatus = $appointment->status;
        $appointment->update([
            'status' => 'in_progress',
            'approved_at' => now(),
            'started_at' => now(),
        ]);
        $this->recordGroomingActivity($appointment, 'grooming_approved', 'Approved grooming request for ' . $appointment->pet()->value('name') . '.', ['status' => $previousStatus], ['status' => $appointment->status]);

        return back()->with(
            'success',
            'Grooming request accepted. Grooming is now in progress.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIVE PET FORM
    |--------------------------------------------------------------------------
    */

    public function receiveForm(GroomingAppointment $appointment)
    {
        if ($appointment->status !== 'approved') {
            return redirect()
                ->route('grooming.index')
                ->with(
                    'error',
                    'Only approved grooming requests can be received.'
                );
        }

        $appointment->load([
            'customer',
            'pet',
            'service',
        ]);

        $petSpecies = $appointment->pet->species ?: 'Dog';
        $serviceSize = $petSpecies === 'Dog' ? ($appointment->pet_size ?: $appointment->pet->weight_category) : 'Any';
        $groomingService = $appointment->service ?? Service::query()
            ->where('active', true)
            ->where('species', $petSpecies)
            ->where('size', $serviceSize)
            ->orderBy('name')
            ->first();

        return view(
            'grooming.receive',
            compact('appointment', 'groomingService')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIVE PET
    |--------------------------------------------------------------------------
    */

    public function receive(
        Request $request,
        GroomingAppointment $appointment
    ) {
        if ($appointment->status !== 'approved') {
            return back()->with(
                'error',
                'Only approved grooming requests can be received.'
            );
        }

        /*
         * FIND PRICE BASED ON THE CUSTOMER'S SELECTED PET SIZE
         */

        $appointment->load(['pet', 'service']);

        $groomingService = $appointment->service;
        if (!$appointment->sale_id && !$groomingService) {
            $petSpecies = $appointment->pet->species ?: 'Dog';
            $serviceSize = $petSpecies === 'Dog' ? ($appointment->pet_size ?: $appointment->pet->weight_category) : 'Any';
            $groomingService = Service::query()
                ->where('active', true)
                ->where('species', $petSpecies)
                ->where('size', $serviceSize)
                ->orderBy('name')
                ->first();
        }

        if (!$groomingService) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No active service is configured for the selected pet size.'
                );
        }

        $groomingPrice = (float) ($appointment->sale_id
            ? $appointment->grooming_price
            : $groomingService->rate);

        $previousStatus = $appointment->status;
        DB::transaction(function () use (
            $appointment,
            $groomingService,
            $groomingPrice
        ) {
            /*
             * SAVE GROOMING INFORMATION
             */

            $appointment->update([
                ...(!$appointment->sale_id ? [
                    'service_id' => $groomingService->id,
                    'grooming_price' => $groomingPrice,
                ] : []),
                'received_at' => now(),
                'status' => 'received',
            ]);
        });

        $this->recordGroomingActivity($appointment, 'grooming_received', 'Received pet for grooming: ' . $appointment->pet()->value('name') . '.', ['status' => $previousStatus], ['status' => $appointment->status]);

        return redirect()
            ->route('grooming.index')
            ->with(
                'success',
                'Pet received successfully. Grooming price: ₱' .
                number_format(
                    $groomingPrice,
                    2
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | START GROOMING
    |--------------------------------------------------------------------------
    */

    public function start(GroomingAppointment $appointment)
    {
        if ($appointment->status !== 'received') {
            return back()->with(
                'error',
                'Only received pets can start grooming.'
            );
        }

        $previousStatus = $appointment->status;
        $appointment->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        $this->recordGroomingActivity($appointment, 'grooming_started', 'Started grooming for ' . $appointment->pet()->value('name') . '.', ['status' => $previousStatus], ['status' => $appointment->status]);

        return back()->with(
            'success',
            'Grooming has started.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | READY
    |--------------------------------------------------------------------------
    */

    public function ready(GroomingAppointment $appointment)
    {
        if (!in_array($appointment->status, ['approved', 'received', 'in_progress'], true)) {
            return back()->with(
                'error',
                'Only active grooming requests can be marked ready.'
            );
        }

        $previousStatus = $appointment->status;
        $appointment->update([
            'status' => 'completed',
            'ready_at' => now(),
            'completed_at' => now(),
        ]);
        $this->recordGroomingActivity($appointment, 'grooming_completed', 'Marked grooming complete for ' . $appointment->pet()->value('name') . '.', ['status' => $previousStatus], ['status' => $appointment->status]);

        return back()->with(
            'success',
            'Grooming is completed. Collect payment if it is still due.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */

    public function cancel(GroomingAppointment $appointment)
    {
        $previousStatus = $appointment->status;
        $result = DB::transaction(function () use ($appointment) {
            $lockedAppointment = GroomingAppointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            $appointments = $lockedAppointment->visit_group_id
                ? GroomingAppointment::query()
                    ->where('visit_group_id', $lockedAppointment->visit_group_id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                : collect([$lockedAppointment]);

            if ($appointments->contains(fn ($item) => $item->sale_id)) {
                return 'paid';
            }

            if ($appointments->contains(fn ($item) => $item->status === 'completed')) {
                return 'completed';
            }

            if ($appointments->contains(fn ($item) => in_array($item->status, ['pending', 'in_progress'], true))) {
                return 'not_cancellable';
            }

            $appointments->each(fn ($item) => $item->update(['status' => 'cancelled']));

            return 'cancelled';
        });

        if ($result === 'paid') {
            return back()->with('error', 'This grooming visit has already been paid. Process a refund before cancelling it.');
        }

        if ($result === 'completed') {
            return back()->with('error', 'A completed grooming visit cannot be cancelled.');
        }

        if ($result === 'not_cancellable') {
            return back()->with('error', 'Pending or in-progress grooming visits cannot be cancelled.');
        }

        $this->recordGroomingActivity($appointment, 'grooming_cancelled', 'Cancelled grooming visit for ' . $appointment->pet()->value('name') . '.', ['status' => $previousStatus], ['status' => 'cancelled']);

        return back()->with(
            'success',
            'Grooming visit has been cancelled.'
        );
    }

    public function waiverPhoto(GroomingAppointment $appointment)
    {
        abort_unless(
            $appointment->waiver_photo_path &&
            Storage::disk('local')->exists($appointment->waiver_photo_path),
            404
        );

        return Storage::disk('local')->download($appointment->waiver_photo_path);
    }

    private function serviceMatchesPet(Service $service, string $species, string $weightCategory): bool
    {
        $expectedSize = $species === 'Dog' ? $weightCategory : 'Any';

        return ($service->species ?? 'Dog') === $species && $service->size === $expectedSize;
    }

    private function recordGroomingActivity(
        GroomingAppointment $appointment,
        string $action,
        string $description,
        array $oldValues,
        array $newValues
    ): void {
        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => $action,
            'module' => 'Grooming',
            'record_type' => 'GroomingAppointment',
            'record_id' => $appointment->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'NEO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Sale::where('invoice_number', $number)->exists());

        return $number;
    }
}