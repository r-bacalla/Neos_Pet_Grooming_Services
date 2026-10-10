<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return redirect()->route('settings.edit');
    }

    public function edit()
    {
        return view('settings.edit', [
            'expiryWarningDays' => Setting::expiryWarningDays(),
            'expiryDiscountPercent' => Setting::expiryDiscountPercent(),
            'expiryDiscountEnabled' => Setting::expiryDiscountEnabled(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'expiry_warning_days' => ['required', 'integer', 'min:1', 'max:365'],
            'expiry_discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'expiry_discount_enabled' => ['nullable', 'boolean'],
        ]);

        Setting::setValue(
            'expiry_warning_days',
            $validated['expiry_warning_days']
        );

        Setting::setValue(
            'expiry_discount_percent',
            $validated['expiry_discount_percent']
        );

        Setting::setValue(
            'expiry_discount_enabled',
            $request->boolean('expiry_discount_enabled') ? 1 : 0
        );

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Expiry settings updated successfully.');
    }
}