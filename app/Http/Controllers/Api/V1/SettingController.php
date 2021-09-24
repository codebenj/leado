<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Rules\EmailString;
use Illuminate\Support\Facades\Storage;
use App\Setting;
use DateTime;
use DateTimeZone;
use File;
use Response;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Image;

class SettingController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function getSettingsByKey($key){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')){
            //admin-email-notification-receivers
            $data = Setting::where('key', $key)->first();

            if(!$data){
                if($key == 'company-name'){
                    $data = Setting::firstOrCreate(['key' => 'company-name', 'value' => '', 'name' => 'Company Name']);
                }
            }

            return response()->json([
                'success' => true,
                'message' =>  __('settings.has_record'),
                'data' => $data,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $settings = Setting::getSetting($request->all());
            $totalSettings = $settings->count();

            $settings = $settings
                ->offset(($pageNo -1) * $pageSize)
                ->limit($pageSize)
                ->get();

            if($totalSettings > 0){
                return response()->json([
                    'success' => true,
                    'message' =>  __('settings.has_record'),
                    'data' => [
                        'settings' => $settings,
                        'total' => $totalSettings,
                        'user' => Auth::user(),
                    ],
                ]);
            }
            return response()->json([
                'success' => true,
                'message' =>  __('settings.no_record'),
                'data' => [
                    'settings' => $settings,
                    'total' => $totalSettings,
                ],
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $rules = [
                'key' => 'required|unique:settings',
                'name' => 'required',
                'value' => 'required',
            ];

            $request->validate($rules);

            $request->merge(['metadata' => $request->metadata]);
            $setting = Setting::create($request->all());

            return response()->json([
                'success' => true,
                'message' =>  __('settings.save_successfully'),
                'data' => $setting
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $setting = Setting::find($id);

            if($setting){
                return response()->json([
                    'success' => true,
                    'message' =>  __('settings.save_successfully'),
                    'data' => $setting
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('settings.no_record'),
                'data' => null
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $setting = Setting::find($id);

            if($setting){

                $rules = [
                    'key' => 'required|unique:settings,key,'.$id,
                    'name' => 'required',
                    'value' => 'required',
                ];

                $request->merge(['metadata' => $request->metadata]);
                $setting->fill($request->all())->save();

                return response()->json([
                    'success' => true,
                    'message' =>  __('settings.update_successfully'),
                    'data' => $setting
                ]);
            }
            return response()->json([
                'success' => false,
                'message' =>  __('settings.no_record'),
                'data' => null
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $setting = Setting::find($id);

            if($setting){
                $setting->delete();

                return response()->json([
                    'success' => true,
                    'message' =>  __('settings.delete_successfully'),
                    'data' => $setting
                ]);
            }
            return response()->json([
                'success' => false,
                'message' =>  __('settings.no_record'),
                'data' => $setting
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    public function uploadLogo(Request $request){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg'
            ]);

            try {
                # Upload file
                $original_file = $request->file('image');
                $base_path = "/images/";
                $image = Image::make($request->file('image'));

                # final image path
                $final_image = $base_path . 'brand-logo.' . $original_file->getClientOriginalExtension();

                # Check if dir exists
                if(!\file_exists(public_path() . $base_path)) {
                    \mkdir(public_path() . $base_path);
                }

                $setting = Setting::firstOrCreate(['key' => 'main-logo']);
                $setting->name = 'Main Logo';
                $setting->value = url($final_image);
                $setting->metadata = [
                    'type' => '',
                    'level' => '',
                    'status' => '',
                    'description' => ''
                ];
                $setting->save();

                # Save file
                $image->save(public_path() . $final_image, 75);

                return response()->json(['success' => true, 'message' => __('settings.upload_logo_successful')], 200);
            } catch(\Exception $e) {
                \Log::error('Logo Upload Error:');
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());

                return response()->json(['success' => false, 'message' => __('settings.upload_logo_unsuccessful')], 400);
            }
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    public function saveEmailReceivers(Request $request) {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {

            $request->validate([
                'receivers' => ['required', 'string', new EmailString],
                'enquirer_message' => ['required']
            ]);

            $setting = Setting::firstOrCreate(['key' => 'admin-email-notification-receivers']);
            $setting->name = 'Admin Email Notification Receivers';
            $setting->value = $request->receivers;
            $setting->metadata = [
                'type' => '',
                'level' => '',
                'status' => '',
                'description' => '',
            ];

            $setting->save();

            $setting = Setting::firstOrCreate(['key' => 'admin-enquire-message']);
            $setting->name = 'Admin Enquire Message Template';
            $setting->value = $request->enquirer_message;
            $setting->metadata = [
                'type' => '',
                'level' => '',
                'status' => '',
                'description' => '',
            ];

            $setting->save();

            //manual notifications organisation
            $now = new DateTime();
            $setting = Setting::firstOrCreate(['key' => 'manual-notifications-organisation']);
            $setting->name = 'Manual Notification Organisation';
            $setting->value = $request->day_of_week . ', ' . $request->time;
            $time = explode(':', $request->time);
            $hour = $time[0];
            $minute = $time[1];
            $now->setTime($hour, $minute);
            $time_to_save = substr($now->format('c'), 0, 19);
            $timezone = Auth::user()->metadata['timezone'] ?? 'Asia/Manila';
            $now->setTimezone(new DateTimeZone($timezone));
            $time_to_save .= $now->format('P');
            $now->setTimezone(new DateTimeZone('UTC'));

            $setting->metadata = [
                'day' => $request->day_of_week,
                'hour' => $now->format('H'),
                'minute' => $minute,
                'am_pm' => $request->am_pm,
                'timezone' => $time_to_save,
                'timezone_name' => Auth::user()->metadata['timezone'] ?? 'Asia/Manila',
                'type' => '',
                'level' => '',
                'status' => '',
                'description' => '',
                'admin_tooltip' => 'Day and time when the notification will be sent to organisation',
                'org_tooltip' => 'Day and time when the notification will be sent to organisation',
            ];

            $setting->save();

            //saving company name
            $setting = Setting::firstOrCreate(['key' => 'company-name']);
            $setting->name = 'Company Name';
            $setting->value = $request->company_name;
            $setting->save();

            return response()->json([
                'success' => true,
                'message' =>  __('settings.save_successfully'),
                'data' =>  $setting,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    public function brandLogo(Request $request) {
        $logo = $request->logo;
        $path = Storage::path('public/' . $logo);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
}
