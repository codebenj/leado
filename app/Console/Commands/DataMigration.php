<?php

namespace App\Console\Commands;

use App\Address;
use App\Country;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Notification;
use App\Organisation;
use App\OrganizationComment;
use App\OrganizationPostcode;
use App\OrganizationUser;
use App\Role;
use App\Setting;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;

class DataMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:data-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute data migration from Traleado v2 to v3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Parse lead escalation expiration
     *
     * @return string
     */
    private function getExpiration($lead_escalation, $setting) {
        $expiration = '';
        if($setting) {
            $expiration = Carbon::parse($lead_escalation->created_at)->{'add' . ucfirst($setting->time_type)}($setting->value)->format('Y-m-d');
        }
        return $expiration;
    }

    private function fetchGeomapData($address) {
        return Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address'              => $address,
            'new_forward_geocoder' => 'true',
            'key'                  => 'AIzaSyCTuOVB720DTWz74jYjK1lAUKZZFdZMpGI',
        ]);
    }

    private function getFullAddress($address, $suburb, $state, $postcode, $retry = true) {
        $partial_address = $address . $suburb . $state . $postcode;
        $partial_address = str_replace(' ', '+', $partial_address);

        $response = $this->fetchGeomapData($partial_address);

        if($response->successful()) {
            $data = $response->json();

            $final_address = [
                'address'  => '',
                'suburb'   => '',
                'city'     => '',
                'state'    => '',
                'country'  => '',
                'postcode' => '',
            ];

            if($data['status'] == 'ZERO_RESULTS' && $retry) {
                $partial_address = $address . $suburb . $state;
                $partial_address = str_replace(' ', '+', $partial_address);
                $this->getFullAddress('', $suburb, $state, $postcode, false);
            }

            if(isset($data['results']) && isset($data['results'][0]['address_components'])) {
                $parts = [
                    'address'  => ['street_number', 'route'],
                    'suburb'   => ['locality'],
                    'city'     => ['administrative_area_level_2'],
                    'state'    => ['administrative_area_level_1'],
                    'country'  => ['country'],
                    'postcode' => ['postal_code'],
                ];

                foreach($parts as $key => $types) {
                    foreach($types as $type_key => $type) {
                        foreach($data['results'][0]['address_components'] as $comp_key => $component) {
                            if(in_array($type, $component['types'])) {
                                $final_address[$key] .= $component['short_name'];

                                if($type_key < (count($types) - 1)) {
                                    $final_address[$key] .= ' ';
                                }
                            }
                        }
                    }
                }
            }

            return $final_address;
        } else {
            return ['error' => true, 'message' => $response->body()];
        }
    }

    /**
     * Check escalation discontinued or abandoned is latest one.
     *
     * @return string
     */
    private function isLeadDiscontinueAbandonedActive($lead_escalation, $lead) {

        if($lead_escalation->lr_status == 'Discontinued' || $lead_escalation->lr_status == 'Abandoned') {
            $assigned_org_latest = DB::connection('traleado_v2')
                ->select('SELECT * FROM assigned_organizations WHERE lead_id = ? ORDER BY id DESC', [$lead->id]);
            
            if(count($assigned_org_latest) > 1) {
                $assigned_org_latest = $assigned_org_latest[0];

                # Check if discontinued or abandoned is the latest escalation
                return ($assigned_org_latest->id == $lead_escalation->assigned_organization_id) ?? false;
            }
        }
        
        return $lead_escalation->is_active;
    }

    private function specialOpportunityLead($lead, $lead_escalation) {
        if($lead_escalation->ls_status == 'Unassigned' && $lead_escalation->lr_status == 'Unassigned') {
            if((isset($lead->commercial) && !empty($lead->commercial))
                || (isset($lead->use_for) && !empty($lead->use_for)) && ($lead->use_for == 'I am a builder' || $lead->use_for == 'I am a tradesperson')) {

                return 'Special Opportunity';
            }
        }

        return $lead_escalation->lr_status;
    }

    private function removeOrgAdminPrefix($string) {
        $string = str_replace('Org', '', $string);
        $string = str_replace('Admin', '', $string);

        return $string;
    }

    /**
     * Execute the command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->info('Data migration is starting...');
            $this->info('Cleaning data...');
    
            # Copy Users
            $users = DB::connection('traleado_v2')
                ->select('SELECT * FROM users');
    
            # Initialize V3 Roles
            $org_role = Role::where('name', 'organisation')->first();
            $admin_role = Role::where('name', 'administrator')->first();

            # Country
            $country = Country::where('name', 'Australia')->first();
    
            # Clean Data
            $exitCode = Artisan::call('migrate:fresh --seed');

            $this->info('----------------------------');
            $this->info('[Settings]');

            # Settings
            $settings = DB::connection('traleado_v2')
                ->select('SELECT * FROM settings');

            $v2_setting = [];
            foreach($settings as $setting) {
                if($setting->key == 'status-awaiting-response-tried-contacting') {
                    $cec_ar_tried = Setting::where('key', 'cec-awaiting-response-tried')->first();

                    if($cec_ar_tried) {
                        $metadata = $cec_ar_tried->metadata;
                        $metadata['type'] = $setting->time_type;

                        $cec_ar_tried->metadata = $metadata;
                        $cec_ar_tried->value = $setting->value;
                        $cec_ar_tried->save();
                    }
                } else if($setting->key ==  'admin-notification-receivers'){
                    $email_receiver_setting = Setting::where('key', 'admin-email-notification-receivers')->first();

                    if($email_receiver_setting) {
                        $email_receiver_setting->value = $setting->value;
                        $email_receiver_setting->save();
                    }
                } else {
                    $new_settings = Setting::whereNotIn('key', ['admin-email-notification-receivers', 'cec-awaiting-response-tried', 'main-logo'])
                                           ->where('name', "{$setting->level} {$setting->status}")->first();

                    $v2_setting["{$setting->level} {$setting->status}"] = $setting;
                    if($new_settings) {
                        $metadata = $new_settings->metadata;
                        $metadata['type'] = $setting->time_type;

                        $email_receiver_setting->metadata = $metadata;
                        $email_receiver_setting->value = $setting->value;
                        $email_receiver_setting->save();
                    }
                }
            }
    
            if($exitCode) {
                $this->info('Error refreshing data. Please try again.');
    
                return 0;
            }
    
            $this->info('----------------------------');
            $this->info('[Admin/Organization]');
    
            $new_orgs = [];
            # Insert new users from v2
            foreach($users as $user) {
                $splitName = explode(' ', $user->name, 2);
                $first_name = array_shift($splitName);
                $last_name  = implode(" ", $splitName);

                if($user->user_type == 'Organization') {
                    $this->info('[Processing] Organization: ' . $user->name);
    
                    $org = DB::connection('traleado_v2')
                        ->select('SELECT * FROM organizations WHERE id = ?', [$user->organization_id]);
                    $org = $org[0];
                    $splitName = explode(' ', $org->contact_name, 2);
                    $first_name = array_shift($splitName);
                    $last_name  = implode(" ", $splitName);
    
                    $new_user = User::create([
                        'first_name'     => $first_name,
                        'last_name'      => $last_name,
                        'email'          => config('app.debug') ? ('test_' . $user->email) : $user->email,
                        'password'       => $user->password,
                        'remember_token' => $user->remember_token,
                        'created_at'     => $user->created_at,
                        'updated_at'     => $user->updated_at,
                        'deleted_at'     => $user->deleted_at,
                        'role_id'        => $org_role->id
                    ]);

                    $new_user->assignRole('organisation');
    
                    $org_postcode = DB::connection('traleado_v2')
                        ->select('SELECT * FROM organization_postcode WHERE orgId = ?', [$user->organization_id]);
    
                    if(count($org_postcode) > 0) {
                        $org_postcode = $org_postcode[0];
                    }
    
                    $new_addr = Address::create([
                        'address'    => '',
                        'city'       => '',
                        'country_id' => $country->id,
                        'state'      => $org->state,
                        'postcode'   => $org_postcode->postcode ?? ''
                    ]);
    
                    $new_org = Organisation::create([
                        'user_id'        => $new_user->id,
                        'name'           => $org->company_name,
                        'org_code'       => $org->org_code,
                        'is_suspended'   => $org->is_suspended,
                        'contact_number' => config('app.debug') ? '' : $org->contact_number,
                        'address_id'     => $new_addr->id
                    ]);

                    $org_user = OrganizationUser::create([
                        'user_id'         => $new_user->id,
                        'organisation_id' => $new_org->id,
                    ]);

                    # Save old org id to be used on creating lead below
                    $new_orgs[$org->id] = $new_org->id;

                    $this->info('----------------------------');
                    $this->info('[Processing] Org Comments');

                    $org_comments = DB::connection('traleado_v2')
                        ->select('SELECT * FROM organization_comments WHERE org_id = ?', [$org->id]);

                    $new_org_comments = [];
                    foreach($org_comments as $key => $org_comment) {
                        $new_org_comments[] = [
                            'organisation_id'  => $new_org->id,
                            'user_id'          => $new_user->id,
                            'comment'          => $org_comment->organization_comments_comment,
                            'created_at'       => $org_comment->created_at,
                            'updated_at'       => $org_comment->updated_at,
                        ];
                    }

                    if(count($new_org_comments)) {
                        OrganizationComment::insert($new_org_comments);
                    }
                
                    $this->info('[Done] Organization: ' . $user->email);
                } else if($user->email != 'admin@traleado.com') {
                    $this->info('[Processing] Admin: ' . $user->name);
    
                    $new_user = User::create([
                        'first_name'     => $first_name,
                        'last_name'      => $last_name,
                        'email'          => config('app.debug') ? ('test_' . $user->email) : $user->email,
                        'password'       => $user->password,
                        'remember_token' => $user->remember_token,
                        'created_at'     => $user->created_at,
                        'updated_at'     => $user->updated_at,
                        'deleted_at'     => $user->deleted_at,
                        'role_id'        => $admin_role->id
                    ]);

                    $new_user->assignRole('administrator');
    
                    $this->info('[Done] Admin: ' . $user->email);
                }
            }
    
            $this->info('----------------------------');
            $this->info('[Leads/Lead Escalations]');
    
            # Copy Leads
            $leads = DB::connection('traleado_v2')
                ->select('SELECT l.*
                FROM leads l
                LEFT JOIN assigned_organizations ao
                    ON ao.lead_id = l.id
                LEFT JOIN leads_responses lr ON lr.assigned_organization_id=ao.id
                    WHERE ao.organization_id IS NULL OR ao.organization_id IS NOT NULL
                GROUP BY l.id;');

            $this->info('Total Leads Found: ' . count($leads));
            $this->info('----------------------------');

            foreach($leads as $key => $lead) {
                if(strpos(strtolower($lead->en_firstname), 'test') === false && strpos(strtolower($lead->en_lastname), 'test') === false) {
                    $this->info('[Processing] Lead #: ' . $lead->id);
    
                    $parsed_address = $this->getFullAddress($lead->street, $lead->suburb, $lead->state, $lead->postcode);
    
                    \Log::info('Lead:' . $lead->id);
                    \Log::info($parsed_address);
    
                    $new_lead = [
                        'id'                    => $lead->id,
                        'cef_id'                => $lead->cef_id,
                        'customer_type'         => $lead->customer_type,
                        'house_type'            => $lead->house_type,
                        'roof_preference'       => $lead->roof_preference,
                        'commercial'            => $lead->commercial,
                        'gutter_edge_meters'    => $lead->metres_gutter_edge,
                        'valley_meters'         => $lead->metres_valley,
                        'source'                => $lead->heard_about_us,
                        'sale_string'           => ('$' . number_format($lead->sale, 2)), # AUS dollar format as default
                        'sale'                  => $lead->sale,
                        'staff_comments'        => $lead->staff_comments,
                        'comments'              => $lead->comments,
                        'situation'             => $lead->situation,
                        'enquirer_message'      => $lead->enquirer_message ?? 'We\'ll organise someone to contact you regarding your Installation query. If you don\'t receive a call within 24hrs, please let us know immediately on 1300 334 333 or send an email to office@leafstopper.com.au',
                        'received_via'          => $lead->recieved_via,
                        'notify_enquirer'       => $lead->flag == '' ? false : true,
                        'use_for'               => $lead->use_for,
                        'source_comments'       => $lead->heard_other,
                        'metadata'              => [],
                        'created_at'            => $lead->created_at,
                        'updated_at'            => $lead->updated_at,
                        'deleted_at'            => $lead->deleted_at,
                        'customer'              => [
                            'first_name'      => $lead->en_firstname ?? '',
                            'last_name'       => $lead->en_lastname ?? '',
                            'contact_number'  => config('app.debug') ? '' : $lead->contact_number,
                            'email'           => config('app.debug') ? ('test_' . $lead->email) : $lead->email,
                        ],
                        'address'               => [
                            'address'         => $parsed_address['address']  ?? ($lead->street   ?? ''),
                            'suburb'          => $parsed_address['suburb']   ?? ($lead->suburb   ?? ''),
                            'city'            => $parsed_address['city']     ?? '',
                            'state'           => $parsed_address['state']    ?? ($lead->state    ?? ''),
                            'postcode'        => $parsed_address['postcode'] ?? ($lead->postcode ?? ''),
                            'country_id'      => $country->id
                        ],
                        'lead_escalations'      => [],
                        'lead_comments'      => [],
                        'lead_job_comments'  => [],
                        'metadata'           => [
                            'source'          => 'v2',
                            'old_data'        => [
                                'lead_id'     => $lead->id
                            ]
                        ]
                    ];
    
                    # Lead Comments
                    $lead_comments = DB::connection('traleado_v2')
                        ->select('SELECT * FROM lead_comments WHERE lead_id = ?', [$lead->id]);
    
                    foreach($lead_comments as $key => $lead_comment) {
                        $new_lead['lead_comments'][] = [
                            'lead_id'       => $lead_comment->lead_id,
                            'user_id'       => $lead_comment->user_id,
                            'comment'       => $lead_comment->lead_comments_comment,
                            'created_at'    => $lead_comment->created_at,
                            'updated_at'    => $lead_comment->updated_at,
                        ];
                    }
    
                    # Lead Job Comments
                    $lead_job_comments = DB::connection('traleado_v2')
                        ->select('SELECT aoc.*, ao.organization_id FROM assign_organization_comments aoc
                            LEFT JOIN assigned_organizations ao
                            ON aoc.assigned_organization_id = ao.id WHERE aoc.lead_id = ?', [$lead->id]);
    
                    foreach($lead_job_comments as $key => $lead_job_comment) {
                        $new_lead['lead_job_comments'][] = [
                            'lead_id'           => $lead_job_comment->lead_id,
                            'sale'              => $lead->sale,
                            'organisation_id'   => $lead_job_comment->organization_id,
                            'comments'          => $lead_job_comment->comment,
                            'created_at'        => $lead_job_comment->created_at,
                            'updated_at'        => $lead_job_comment->updated_at,
                        ];
                    }
        
                    # Lead Escalations
                    $lead_escalations = DB::connection('traleado_v2')
                        ->select('SELECT
                                    ls.status as ls_status,
                                    lr.status as lr_status,
                                    ls.id as ls_id,
                                    lr.id as lr_id,
                                    ls.is_active as is_active,
                                    ls.color as color,
                                    ao.organization_id as organization_id,
                                    lr.*
                                FROM
                                    leads_status as ls
                                    LEFT JOIN leads_responses as lr ON lr.lead_status_id = ls.id
                                    LEFT JOIN assigned_organizations as ao ON ls.assigned_organization_id = ao.id
                                    AND lr.assigned_organization_id = ao.id WHERE ao.lead_id = ?;', [$lead->id]);
        
                    foreach($lead_escalations as $lead_escalation) {
                        $new_lead_escalation = [
                            'escalation_level'      => ucwords($lead_escalation->ls_status),
                            'escalation_status'     => $this->specialOpportunityLead($lead, $lead_escalation),
                            'is_active'             => $this->isLeadDiscontinueAbandonedActive($lead_escalation, $lead),
                            'color'                 => $lead_escalation->color == 'gray' ? 'purple' : $lead_escalation->color,
                            'progress_period_date'  => $lead_escalation->progress_period_date,
                            'gutter_edge_meters'    => $lead_escalation->no_mtrs_gutter_edge,
                            'valley_meters'         => $lead_escalation->no_mtrs_valley,
                            'installed_date'        => $lead_escalation->date_of_installation,
                            'is_notified'           => $lead_escalation->is_send,
                            'reason'                => $lead_escalation->reason ?? ($lead_escalation->description ?? ($lead_escalation->name ?? '')),
                            'comments'              => $lead_escalation->comments,
                            'metadata'              => json_encode([
                                'v2_data' => true,
                                'old_data' => [
                                    'lead_status_id' => $lead_escalation->ls_id,
                                    'lead_response_id' => $lead_escalation->lr_id
                                ]
                            ]),
                            'created_at'            => $lead_escalation->created_at,
                            'updated_at'            => $lead_escalation->updated_at,
                            'deleted_at'            => $lead->deleted_at,
                            'expiration_date'       => $this->getExpiration($lead_escalation, $v2_setting["{$lead_escalation->ls_status} {$lead_escalation->lr_status}"] ?? false),
                        ];
    
                        if(isset($lead_escalation->organization_id) && isset($new_orgs[$lead_escalation->organization_id])) {
                            $new_lead_escalation['organisation_id'] = $new_orgs[$lead_escalation->organization_id];
                        }
    
                        $new_lead['lead_escalations'][] = $new_lead_escalation;
                    }
        
                    \App\Jobs\CreateLeadsJob::dispatch($new_lead);
    
                    $this->info('[Done] Lead #: ' . $lead->id);
                }
            }

            $this->info('----------------------------');
            $this->info('[Processing] Notifications');

            # Notifications
            $notifications = DB::connection('traleado_v2')
                    ->select('SELECT * FROM notifications ORDER BY id');

            # Lead Escalation Model Instance
            $le_model_instance = new LeadEscalation();

            $new_notifications = [];
            foreach($notifications as $key => $notification) {
                $new_org = null;
                if($notification->notification_type == 'Notify Organisation') {
                    $org = DB::connection('traleado_v2')
                    ->select('SELECT * FROM organizations WHERE id = ?', [$notification->user_id]);
                    
                    if($org) {
                        $org = $org[0];
                        
                        $new_org = Organisation::where('org_code', $org->org_code)->first();
                    }
                }
                
                $new_notifications[] = [
                    'title'                 => addslashes($notification->title) ?? '',
                    'description'           => addslashes($notification->message) ?? '',
                    'metadata'              => json_encode([
                        'to'                     => ($notification->notification_type == 'Notify Organisation' ? 'organization' : 'admin'),
                        'lead_id'                => $notification->lead_id ?? '',
                        'organisation_id'        => $new_org->id ?? 0,
                        'notification_type'      => $notification->notification_type,
                        'lead_escalation_status' => $notification->status ?? '',
                        'lead_escalation_color'  => $le_model_instance->leadEscalationStatus($notification->status) ?? 'purple',
                    ]),
                    'is_read'               => $notification->is_read ?? '',
                    'is_read_organization'  => false,
                    'created_at'            => $notification->created_at ?? '',
                    'updated_at'            => $notification->updated_at ?? '',
                ];

            }

            if(count($new_notifications) > 0) {
                Notification::insert($new_notifications);
            }
            $this->info('[DONE] Created ' . count($notifications) . ' Notifications');

            $this->info('----------------------------');
            $this->info('[Processing] Activity Log');

            # Notifications
            $activity_log = DB::connection('traleado_v2')
                    ->select('SELECT * FROM activity_log');

            $new_activity_log = [];
            foreach($activity_log as $key => $log) {
                $metadata = json_decode($log->properties);
                $new_org = null;
                if(isset($metadata->org_name) && !empty($metadata->org_name)) {
                    $new_org = Organisation::where(DB::raw('LOWER(name)'), strtolower($metadata->org_name))->first();
                }

                $new_activity_log[] = [
                    'log_name'          => 'default',
                    'causer_type'       => 'App\User',
                    'causer_id'         => User::find(1)->id,
                    'subject_type'      => 'App\LeadEscalation',
                    'subject_id'        => '1',
                    'description'       => 'escalation',
                    'properties'        => json_encode([
                        'title'                 => $metadata->title ?? '',
                        'description'           => $metadata->message ?? '',
                        'metadata'              => [
                            'to'                     => (strpos($metadata->notification_type, 'Org') !== false ? 'organization' : ((strpos($metadata->notification_type, 'Admin') !== false ? 'admin' : 'enquirer'))),
                            'lead_id'                => $metadata->lead_id ?? '',
                            'organisation_id'        => $new_org->id ?? 0,
                            'notification_type'      => ($metadata->notification_type ? $this->removeOrgAdminPrefix($metadata->notification_type) : ''),
                            'lead_escalation_status' => $metadata->lead_status ?? '',
                            'lead_escalation_color'  => $metadata->lead_color ?? '',
                        ]
                    ]),
                    'created_at'        => $log->created_at,
                    'updated_at'        => $log->updated_at,
                ];

            }
            Activity::insert($new_activity_log);
            $this->info('[DONE] Created ' . count($new_activity_log) . ' Activity Log Rows');
        } catch(\Exception $e) {
            $this->error('[ERROR]: ' . $e->getMessage());
            $this->error('[ERROR]: ' . $e->getTraceAsString());
        }
    }

    /**
    * The job failed to process.
    *
    * @param  Exception  $exception
    * @return void
    */
    public function failed(Exception $exception)
    {
        \Log::error($exception->getMessage());
        \Log::error($exception->getTraceAsString());
    }
}
