<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', ['settings' => ApplicationSetting::orderBy('key')->get()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'low_stock_default_threshold_days' => 'nullable|integer|min:1',
            'expiry_alert_days' => 'nullable|integer|min:1',
            'currency_symbol' => 'nullable|string|max:5',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                ApplicationSetting::set($key, (string) $value, is_int($value) ? 'int' : 'string');
            }
        }

        return back()->with('success', 'Settings updated.');
    }
}
