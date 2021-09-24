<?php

namespace App;

use App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\NotificationCreateEvent;
class Notification extends Model
{
    use SoftDeletes;

    protected $table_name = 'notifications';
    protected $fillable = [
        'title', 'description', 'metadata',  'user_id',  'created_at', 'updated_at', 'deleted_at', 'is_read', 'is_read_organization',
    ];
    public $timestamps = true;

     /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($notification) {
            //event(new NotificationCreateEvent($notification));
        });

        static::updated(function ($notification) {
            //event(new NotificationCreateEvent($notification));
        });
    }

    private function getDescription() {

    }

    public function scopeFilterAsRole($query){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $query->whereJsonContains('metadata->to', 'admin');
        }
        else if($user->hasRole('organisation')) {
            $user_org = $user->organisation_user;

            if(!empty($user_org->organisation_id)){
                $query->whereJsonContains('notifications.metadata->to', 'organization');
                $query->whereJsonContains('notifications.metadata->organisation_id', $user_org->organisation_id);
            }else{
                //user has no organiation_user
                $query->whereJsonContains(
                    'notifications.metadata->organisation_id', 0
                );
            }
        }

        return $query;
    }

    public function scopeGetNotifications($query, $request){
        $user = auth()->user();
        if($user->hasRole('organisation')){
            $query->join('organisations', 'organisations.id', '=', 'notifications.metadata->organisation_id')
            ->select('notifications.*', 'organisations.name as org_name')
            ->where(function($q) use($request){
                if( isset($request['is_read']) ){
                    $q->where('is_read_organization', $request['is_read']);
                }
            })
            ->orderBy('id', 'DESC');
        }elseif($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $query->where(function($q) use($request){
                if( isset($request['is_read']) ){
                    $q->where('is_read', $request['is_read']);
                }
            })
            ->orderBy('id', 'DESC');
        }

        return $query;
    }

    public function parseMessages() {
        // if(!\Cache::get('notification_messages')) {
        //     $notification_raw = file_get_contents(storage_path('notification_messages.json'));
        //     $notification = json_decode($notification_raw, true);

        //     \Cache::put(
        //         'notification_messages',
        //         \serialize($notification_raw)
        //     );
        // } else {
        //     $notification = \Cache::get('notification_messages');
        //     $notification = \unserialize($notification);
        //     $notification = json_decode($notification, true);
        // }

        $notification_raw = file_get_contents(storage_path('notification_messages.json'));
        $notification = json_decode($notification_raw, true);

        return $notification;
    }

    public function hasNotify($escalation, $user_type, $notification_type = 'notification') {
        $message = $this->parseMessages();

        if(strpos($escalation, 'In Progress - Extended') !== false) {
            $escalation = 'In Progress - Extended';
        }

        return isset($message[$escalation][$user_type][$notification_type]['title'])
            && isset($message[$escalation][$user_type][$notification_type]['description'])
            && !empty($message[$escalation][$user_type][$notification_type]['title'])
            && !empty($message[$escalation][$user_type][$notification_type]['description'])
            ? true : false;
    }

    public function getMessage($escalation, $user_type, $notification_type = 'notification') {
        $message = $this->parseMessages();

        if(strpos($escalation, 'In Progress - Extended') !== false) {
            $escalation = 'In Progress - Extended';
        }

        return $message[$escalation][$user_type][$notification_type];
    }

    public function logActivity($performed_on, $properties, $log_name = 'escalation') {
        $causer = auth()->user() ?? User::find(1); # Default as admin

        activity()
            ->causedBy($causer)
            ->performedOn($performed_on)
            ->withProperties($properties)->log($log_name);
    }

    public function sendSMS($contact_number, $message) {
        if(env('SMS_ENABLED', false) == false) {
            return;
        }
        if(env('SMSGLOBAL_KEY') && env('SMSGLOBAL_SECRET')) {
            try {
                // get your REST API keys from MXT https://mxt.smsglobal.com/integrations
                \SMSGlobal\Credentials::set(env('SMSGLOBAL_KEY'), env('SMSGLOBAL_SECRET'), env('SMSGLOBAL_FROM') ?? '');

                $sms = new \SMSGlobal\Resource\Sms();

                # for debug purpose
                if(env('SMS_RECEIVER')) {
                    $contact_number = env('SMS_RECEIVER');
                }
                $response = $sms->sendToOne($contact_number, $message);

                return $response;
            } catch(\Exception $e) {
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());

                return false;
            }
        }
    }

    public static function send_sms($contact_number, $message) {
        $sms_global_from = config('app.sms_global_from');
        $sms_global_user = config('app.sms_global_user');
        $sms_global_password = config('app.sms_global_password');
        $sms_enabled = config('app.sms_enabled');

        if(! $sms_enabled) {
            return false;
        }

        # for debug purpose
        if(config('sms_receiver')) {
            $contact_number = config('sms_receiver');
        }

        $client = new \GuzzleHttp\Client();

        $response = $client->get('https://api.smsglobal.com/http-api.php', [
            'query' => [
                'action' => 'sendsms',
                'user' => $sms_global_user,
                'password' => $sms_global_password,
                'from' => $sms_global_from,
                'to' => $contact_number,
                'text' => rawurlencode($message),
                'maxsplit' => 2,
            ]
        ]);

        return $response;
    }
}
