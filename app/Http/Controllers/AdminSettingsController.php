<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;

class AdminSettingsController extends Controller
{
    public function index () {
        $settings = Setting::all();

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function getSetting($id) {
        $setting = Setting::where('id', $id)->first();

        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    public function save (Request $request) {
        $request->validate([
            'status' => 'required',
            'level' => 'required',
            'key' => 'required|unique:settings,key' . ( isset($request->setting_id) ? ",{$request->setting_id}" : "" ),
            'name' => 'required',
        ]);

        $data = [
            'status' => $request->status,
            'level' => $request->level,
            'key' => $request->key,
            'value' => $request->value,
            'description' => $request->description,
            'type' => $request->type,
            'tooltips' => json_encode([
                'admin' => $request->admin_tooltip,
                'org' => $request->org_tooltip
            ]),
        ];

        $setting = Setting::where('id', $request->setting_id)->first();

        if ( isset($setting) ) {
            $setting->update($data);
        } else {
            $setting = Setting::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setting successfully save.',
            'data' => $setting
        ]);
    }

    public function delete (Request $request) {
        $setting = Setting::where('id', $request->setting_id)->first();
        $success = true;
        $message = '';

        if ( isset($setting) ) {
            $setting->delete();
            $message = 'Setting successfully deleted.';
        } else {
            $success = false;
            $message = 'Setting not dound.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);

    }
}
