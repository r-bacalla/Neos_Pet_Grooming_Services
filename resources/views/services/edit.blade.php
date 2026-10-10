@extends('layouts.app')

@section('title', 'Edit Service')

@section('content')

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-4">

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M16.862 4.487a2.121 2.121 0 0 1 3 3L8.25 19.1 4 20l.9-4.25L16.862 4.487Z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M14.5 6.85 17.15 9.5"/>
                    </svg>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            Edit Service
                        </h1>

                        @if($service->active)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                Active
                            </span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Update information, pricing, size, and availability.
                    </p>
                </div>

            </div>

            <a href="{{ route('services.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                Back to Services
            </a>

        </div>


        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

                <div class="flex items-center gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="m5 12 4 4L19 6"/>
                        </svg>
                    </div>

                    <p class="text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </p>

                </div>

            </div>
        @endif


        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">

                <div class="flex gap-3">

                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 9v3.75m0 3.75h.008M10.29 3.86 2.82 17a2 2 0 0 0 1.74 3h14.88a2 2 0 0 0 1.74-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-red-800">
                            Please correct the following errors:
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                </div>

            </div>
        @endif


        {{-- Update Form --}}
        <form method="POST"
              action="{{ route('services.update', $service) }}">

            @csrf
            @method('PUT')
            <input type="hidden" name="available_add_on_ids_present" value="1">
            <input type="hidden" name="catalog_search" value="{{ request('search') }}">
            <input type="hidden" name="catalog_species" value="{{ request('species') }}">
            <input type="hidden" name="catalog_addons" value="{{ request('addons') }}">

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                {{-- Card Header --}}
                <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-7">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M16.862 4.487a2.121 2.121 0 0 1 3 3L8.25 19.1 4 20l.9-4.25L16.862 4.487Z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Service Information
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Update the details used throughout your grooming system.
                            </p>
                        </div>

                    </div>

                </div>


                <div class="space-y-8 p-5 sm:p-7">

                    {{-- Basic Information --}}
                    <section>

                        <div class="mb-5">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Basic Information
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Update the service name and description.
                            </p>
                        </div>


                        <div class="grid grid-cols-1 gap-5">

                            {{-- Service Name --}}
                            <div>

                                <label for="name"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Service Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name', $service->name) }}"
                                       required
                                       autofocus
                                       class="block w-full rounded-xl border px-4 py-3 text-sm text-slate-900 outline-none transition
                                       @error('name')
                                           border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                       @else
                                           border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                       @enderror">
                                    <p class="mt-1.5 text-xs text-slate-500">Keep package variants under the same service name.</p>

                                @error('name')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Description --}}
                            <div>

                                <label for="description"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Description
                                </label>

                                <textarea name="description"
                                          id="description"
                                          rows="5"
                                          placeholder="Describe what is included in this grooming service..."
                                          class="block w-full resize-y rounded-xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400
                                          @error('description')
                                              border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                          @else
                                              border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                          @enderror">{{ old('description', $service->description) }}</textarea>

                                @error('description')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    @if($groomingAddons->where('active', true)->isNotEmpty())
                    <section class="border-t border-slate-200 pt-8">
                        <fieldset class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <legend class="px-1 text-sm font-semibold text-slate-800">Available add-ons</legend>
                            <p class="mb-3 text-xs text-slate-500">Customers can choose these extras with this service. Leave unchecked to offer none.</p>
                            @php($availableAddonIds = old('available_add_on_ids', $service->available_add_on_ids ?? []))
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($groomingAddons->where('active', true) as $addon)
                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                        <input type="checkbox" name="available_add_on_ids[]" value="{{ $addon->id }}" @checked(in_array($addon->id, $availableAddonIds)) class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                                        <span class="min-w-0 flex-1">{{ $addon->name }}</span>
                                        <span class="text-xs text-slate-500">₱{{ number_format((float) $addon->rate, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </section>
                    @endif


                    {{-- Pricing & Size --}}
                    <section class="border-t border-slate-200 pt-8">

                        <div class="mb-5">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Pricing & Size
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Adjust the service rate and pet size.
                            </p>
                        </div>


                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="species" class="mb-2 block text-sm font-semibold text-slate-700">Pet Type <span class="text-red-500">*</span></label>
                                <select name="species" id="species" required class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-4 focus:ring-slate-100">
                                    @foreach(['Dog', 'Cat', 'Other'] as $species)
                                        <option value="{{ $species }}" @selected(old('species', $service->species ?? 'Dog') === $species)>{{ $species }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rate --}}
                            <div>

                                <label for="rate"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Service Rate
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="flex overflow-hidden rounded-xl border bg-white transition
                                    @error('rate')
                                        border-red-300 focus-within:ring-4 focus-within:ring-red-100
                                    @else
                                        border-slate-300 focus-within:border-slate-900 focus-within:ring-4 focus-within:ring-slate-100
                                    @enderror">

                                    <span class="flex items-center border-r border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                                        ₱
                                    </span>

                                    <input type="number"
                                           name="rate"
                                           id="rate"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('rate', $service->rate) }}"
                                           required
                                           class="w-full border-0 bg-transparent px-4 py-3 text-sm text-slate-900 outline-none focus:ring-0">

                                </div>

                                @error('rate')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Size --}}
                            <div>

                                <label for="size"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Service Size
                                    <span class="text-red-500">*</span>
                                </label>

                                <select name="size" id="size" required class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-4 focus:ring-slate-100">
                                    @foreach(['Small', 'Medium', 'Large', 'XLarge', 'Any'] as $size)
                                        <option value="{{ $size }}" @selected(old('size', $service->size) === $size)>{{ $size }}</option>
                                    @endforeach
                                </select>

                                @error('size')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- Service Status --}}
                    <section class="border-t border-slate-200 pt-8">

                        <div class="mb-5">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Service Status
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Control whether this service is available for use.
                            </p>
                        </div>


                        <label for="active"
                               class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-slate-100">

                            <div class="flex items-start gap-3">

                                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M12 6v6l4 2"/>
                                        <circle cx="12"
                                                cy="12"
                                                r="9"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        Service is active
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Active services can be selected when creating grooming appointments and sales.
                                    </p>
                                </div>

                            </div>


                            <div class="relative shrink-0">

                                <input type="checkbox"
                                       name="active"
                                       value="1"
                                       id="active"
                                       class="peer sr-only"
                                       {{ old('active', $service->active) ? 'checked' : '' }}>

                                <div class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500 peer-focus:ring-4 peer-focus:ring-emerald-100"></div>

                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></div>

                            </div>

                        </label>

                        @error('active')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </section>


                    {{-- Information Note --}}
                    <section class="border-t border-slate-200 pt-8">

                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">

                            <div class="flex gap-3">

                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <circle cx="12"
                                                cy="12"
                                                r="9"/>
                                        <path stroke-linecap="round"
                                              d="M12 10v6"/>
                                        <path stroke-linecap="round"
                                              d="M12 7.25h.01"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-blue-900">
                                        Service availability
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-blue-800">
                                        Deactivating a service keeps its existing information while preventing it from being treated as an active service.
                                    </p>
                                </div>

                            </div>

                        </div>

                    </section>

                </div>


                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-5 sm:flex-row sm:items-center sm:justify-end sm:px-7">

                    <a href="{{ route('services.index') }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200 sm:w-auto">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M5 12.75 9.5 17 19 7.5"/>
                        </svg>

                        Save Changes
                    </button>

                </div>

            </div>

        </form>

    </div>
</div>

@endsection