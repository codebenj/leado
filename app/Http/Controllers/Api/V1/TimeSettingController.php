<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TimeSettingRequest;
use App\TimeSetting;
use App\LeadEscalation;
use Carbon\Carbon;

class TimeSettingController extends Controller
{
    public function index() {
        $timeSettings = TimeSetting::all();

        return response()->json([
            'success' => true,
            'data' => $timeSettings
        ]);
    }

    public function save(TimeSettingRequest $request) {
        // validate start and stop dates
        if ( $request->type == false && $this->validateSelectedDates($request) ) {
            return response()->json([
                'success' => false,
                'message' => __('messages.time_setting_invalid_dates')
            ], 422);
        }

        $timeSetting = TimeSetting::where('id', $request->id)->first();

        $data = [
            'name' => $request->name,
            'type' => $request->type ? 'recurring' : 'one-time',
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'stop_date' => $request->stop_date,
            'stop_time' => $request->stop_time,
            'start_day' => $request->start_day,
            'stop_day' => $request->stop_day,
            'is_active' => $request->change_status ? !$request->is_active : $request->is_active,
        ];

        if ( isset($timeSetting) ) {
            $timeSetting->update($data);
        } else {
            $timeSetting = TimeSetting::create($data);
        }

        $pauedTimers = LeadEscalation::setPausedTimers();

        return response()->json([
            'success' => true,
            'data' => $timeSetting,
            'message' => __('messages.time_setting_success_response')
        ]);
    }

    public function delete(Request $request, $id) {
        $pauedTimers = LeadEscalation::removePausedTimers();
        $timeSetting = TimeSetting::find($id);
        $timeSetting->delete();

        return response()->json([
            'success' => true,
            'data' => $timeSetting,
            'message' => __('messages.time_setting_delete_response')
        ]);
    }

    public function validateSelectedDates($request) {
        $startDateTime = Carbon::parse("{$request->start_date} {$request->start_time}");
        $stopDateTime = Carbon::parse("{$request->stop_date} {$request->stop_time}");

        return $startDateTime->gt($stopDateTime);
    }

    public function changeStatus(Request $request, $id) {
        $timeSetting = TimeSetting::where('id', $id)->first();

        if ( isset($timeSetting) ) {
            if (!$timeSetting->is_active) {
                $pauedTimers = LeadEscalation::setPausedTimers();
            } else {
                $pauedTimers = LeadEscalation::removePausedTimers();
            }
            $timeSetting->update([
                'is_active' => !$timeSetting->is_active
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $timeSetting,
            'message' => __('messages.time_setting_success_response')
        ]);
    }
}
