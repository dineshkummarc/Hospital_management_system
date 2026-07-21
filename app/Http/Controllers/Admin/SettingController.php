<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'hospital_name'   => Setting::get('hospital_name', 'Hospital Management System'),
            'sms_sender'      => Setting::get('sms_sender', 'HospitalMS'),
            'contact_email'   => Setting::get('contact_email', 'admin@hospital.com'),
            'contact_phone'   => Setting::get('contact_phone', '0700000000'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hospital_name'   => 'required|string|max:100',
            'sms_sender'      => 'required|string|max:20',
            'contact_email'   => 'required|email|max:100',
            'contact_phone'   => 'required|string|max:20',
        ]);

        foreach ($data as $key => $val) {
            Setting::set($key, $val);
        }

        AuditLog::record('settings_updated', null, [], $data);

        return back()->with('success', 'Hospital configurations updated successfully.');
    }
}
