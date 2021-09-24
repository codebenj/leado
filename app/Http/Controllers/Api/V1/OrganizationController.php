<?php

namespace App\Http\Controllers\Api\V1;

use App\Address;
use App\Organisation;
use App\OrganizationUser;
use App\LeadEscalation;
use App\User;
use App\Country;
use App\Exports\OrganizationsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\ImportLog;
use App\Imports\OrganisationPostcodeImport;
use App\Imports\OrganizationImport;
use App\Jobs\NotifyOrgSuspendedStatusJob;
use App\Lead;
use App\Mail\InquirerNotification;
use App\Notification;
use App\Mail\OrganizationManualNotification;
use App\OrganizationPostcode;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\OrganizationComment;
use Carbon\Carbon;
use Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;

class OrganizationController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function leadStats(Request $request, $org_id){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {

            $data = LeadEscalation::getCurrentLeadsByOrganization($org_id);

            return response()->json([
                'success' => true,
                'message' => __('organisation.has_records'),
                'data' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'as' . __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    /**
     * update organisation availability status
     * @return organisation
     */
    public function availabilityStatus(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            $organisation = Organisation::find($request->org_id);

            if(!$organisation){
                return response()->json([
                    'success' => true,
                    'message' =>  __('organisation.no_records'),
                    'data' => ''
                ]);
            }

            $user = User::find($this->user->id);
            $timezone = $user->metadata['timezone'] ?? 'Australia/Sydney';
            $available_when = Carbon::parse($request->available_when);
            $available_when->setTimezone($timezone);

            $data = [
                'is_available' => $request->is_available,
                'available_when' => $available_when,
                'reason' => $request->reason ?? ''
            ];

            $organisation->fill($data)->save();


            return response()->json([
                'success' => true,
                'message' =>  __('organisation.status_update'),
                'data' => $organisation
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'as' . __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {
            $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
            $pageSize = isset($request->pageSize) ? $request->pageSize : 20;
            $orgs = Organisation::getOrganisation($request->all())
                ->with('address.country', 'user.address')
                ->orderBy('name', 'asc');

            $total = $orgs->count();

            $data = $orgs
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            if ($total > 0) {
                return response()->json([
                    'success' => true,
                    'message' =>  __('organisation.has_records'),
                    'data' => [
                        'organisations' => $data,
                        'total' => $total
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.no_records'),
                'data' => [
                    'organisations' => [],
                    'total' => 0
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'as' . __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    /**
     * Search organizations by lead id, finding address country_id, postcode import, states
     */
    public function getOrganizationByLeadId($lead_id)
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {

            $lead = Lead::with('customer.address')->find($lead_id);

            $organizations = Organisation::orWhereHas('postcodes', function ($q) use ($lead) {
                $q->where('postcode', $lead->customer->address->postcode);
            })->orWhereHas('address', function ($q) use ($lead) {
                $q->where('state', $lead->customer->address->state);
                $q->where('country_id', $lead->customer->address->country_id);
            })->with('address')->orderBy('name', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.has_record'),
                'data' => $organizations
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    // Get orgasniation post codes customers
    public function getOrganizationCustomersPostcodes(Request $request, $id)
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {
            $orgs = Organisation::where('id', $id)->with('lead_escalations.lead.customer.address')->orderBy('name', 'asc');
            $total = $orgs->count();
            $data = $orgs->get();

            if ($total > 0) {
                return response()->json([
                    'success' => true,
                    'message' =>  __('organisation.has_records'),
                    'data' => [
                        'postcodes' => $data,
                        'total' => $total
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.no_records'),
                'data' => [
                    'postcodes' => [],
                    'total' => 0
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'as' . __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function getPostCodesAll()
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {
            $postcodes = OrganizationPostcode::all();

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.has_record'),
                'data' => $postcodes
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function getOrganizationByIds(Request $request)
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {
            if (isset($request->ids)) {
                $orgs = Organisation::whereIn('id', $request->ids)->with('address.country', 'user')->get();
            } else {
                $orgs = [];
            }

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.has_record'),
                'data' => $orgs
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function all()
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')) {
            // $orgs = Organisation::where('org_status', 1)
            //     ->with('address.country', 'user.address')
            //     ->orderBy('name', 'asc')
            //     ->get();

            $orgs = Organisation::with('address.country', 'user.address')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.has_record'),
                'data' => $orgs
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrganizationRequest $request)
    {
        try {
            \DB::beginTransaction();

            $org_code = Organisation::where('org_code', $request->org_code)->withTrashed()->first();

            if($org_code){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.org_code_duplicate'),
                    'data' => []
                ]);
            }

            $country = Country::where('name', 'Australia')->first();

            # Address
            $address             = new Address();
            $address->address    = $request->address ?? '';
            $address->state      = $request->state ?? '';
            $address->city       = $request->city ?? '';
            $address->suburb     = $request->suburb ?? '';
            $address->postcode   = $request->postcode ?? '';
            $address->country_id = $country->id ?? '';
            $address->save();

            # User
            $user = new User();
            $user->email      = $request->email;
            $user->password   = Hash::make($request->password);
            $user->first_name = $request->first_name ?? '';
            $user->last_name  = $request->last_name ?? '';
            $user->role_id = 3; # Default;
            $user->address_id = $address->id;
            $user->save();

            # Assign role
            $user->assignRole('organisation');

            $org_data = $request->only('name', 'landline_contact', 'additional_details', 'contact_number', 'org_code', 'is_suspended');
            $org_data['user_id'] = $user->id;
            $org_data['address_id'] = $address->id;
            $org_data['notifications'] = $request->notifications;
            $org_data['metadata'] = [
                'address_search' => $request->address_search,
                'manual_update' => $request->manual_update,
                'pricing' => $request->pricing,
                'priority' => $request->priority,
            ];

            $org = Organisation::create(
                $org_data
            );

            # Org User
            $org_user = OrganizationUser::create([
                'user_id' => $user->id,
                'organisation_id' => $org->id,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.org_success_response'),
                'data' => $org
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response'),
                'data' => []
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth()->user();
        try {
            $org = Organisation::with( 'address', 'address.country', 'user' )->find( $id );
            $address = Address::where( 'id', $id )->first();
            $systemActivity = Activity::where( 'subject_id', $id )->orderBy( 'id', 'DESC' )->get();

            // $geo_location = Http::get( 'https://maps.googleapis.com/maps/api/geocode/json', [
            //     'address'              => $address['state'] . ', Australia',
            //     'new_forward_geocoder' => 'true',
            //     'key'                  => 'AIzaSyAJuWY68N2EwaVz245Ra5sFMpJll1kjHik',
            // ] );

            // $geo_location = json_decode( $geo_location );

            // if ( $geo_location->status == 'REQUEST_DENIED' ) {
            //     $location = [ 'lat' => -18, 'lng' => 136 ];

            // } else {
            //     if ( isset( $geo_location['results'][0] ) ) {
            //         $location = $geo_location['results'][0]['geometry'];

            //     } else {
            //         $location = ['lat' => -18, 'lng' => 136];
            //     }
            // }

            if (!$org) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.org_not_found_response'),
                    'data' => []
                ], 200);
            }

            if ($user->hasRole('organisation') && !$org->organisation_users()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorize_response'),
                    'data' => []
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $org,
                //'location' =>  $location,
                'systemActivity' => $systemActivity
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response'),
                'data' => []
            ]);
        }
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
        $user = auth()->user();
        try {
            $org = Organisation::with('address', 'user', 'organisation_users')->find($id);

            if (!$org) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.org_not_found_response'),
                    'data' => []
                ], 400);
            }

            if ($user->hasRole('organisation') && !$org->organisation_users()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorize_response'),
                    'data' => []
                ], 401);
            }

            $org_code = Organisation::where('org_code', $request->org_code)->where('id' , '!=', $id)->withTrashed()->first();
            if($org_code){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.org_code_duplicate'),
                    'data' => []
                ]);
            }


            $is_suspended = $request->is_suspended;
            $password = $request->password;
            $manual_update = $request->manual_update;
            $org_decoded = json_decode($org);
            $manual_update_decoded = $org_decoded->metadata->manual_update;

            if ($is_suspended !==  intval($org->is_suspended)) {
                $notif_structure = [
                    'title' => 'Org Suspension Update',
                    'description' => $is_suspended ? 'Org suspended' : 'Org unsuspended',
                    'org_id' => $org->id
                ];

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($org)
                    ->withProperties($notif_structure)->log('system_activity');
            }

            if (isset($request->password) && !Hash::check($password, $org->user->password)) {
                $notif_structure = [
                    'title' => 'Org Password Update',
                    'description' => 'Org password has been changed',
                    'org_id' => $org->id
                ];

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($org)
                    ->withProperties($notif_structure)->log('system_activity');
            }

            if ($manual_update !== $manual_update_decoded) {
                $notif_structure = [
                    'title' => 'Org Escalation',
                    'description' => $manual_update ? 'Org changed to Manual' : 'Org changed to Auto',
                    'org_id' => $org->id
                ];

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($org)
                    ->withProperties($notif_structure)->log('system_activity');
            }

            $request->merge(['on_hold' => !$request->on_hold]);

            $org_data = $request->only('name', 'contact_number', 'landline_contact', 'additional_details', 'org_code', 'is_suspended', 'org_status', 'on_hold', 'priority', 'price');
            $org_data['metadata'] = [
                'address_search' => $request->address_search,
                'manual_update' => $request->manual_update,
                'pricing' => $request->pricing,
                'priority' => $request->priority,
                'suspension_type' => ($request->is_suspended) ? $request->suspension_type : false,
                'low_priority' => (!$request->is_suspended) ? $request->low_priority : true,
            ];

            $org->notifications  = $request->notifications ?? '';

            # Update org
            $org->update($org_data);

            // Send unsuspended notification
            $is_suspended = $org->is_suspended == 1 ? true : false;

            $sendNotification = isset($request->send_org_notification) && !empty($request->send_org_notification) ? 1 : 0;

            if (!$is_suspended && $sendNotification) {
                NotifyOrgSuspendedStatusJob::dispatch($org->id);
            }

            //send organization email notification
            if ($is_suspended) {
                NotifyOrgSuspendedStatusJob::dispatch($org->id);
            }

            // Country
            $country = Country::where(function ($q) use ($request) {
                $q->orWhere('name', $request->country);
                $q->orWhere('name', 'LIKE', "%{$request->country}%");
            })->first();

            // Update org address
            $org->address->address    = $request->address ?? '';
            $org->address->state      = $request->state ?? '';
            $org->address->city       = $request->city ?? '';
            $org->address->suburb     = $request->suburb ?? '';
            $org->address->postcode   = $request->postcode ?? '';
            $org->address->country_id = $country->id ?? '';
            $org->address->save();

            // Update org user
            $org_user = $org->user;


            // mutated in model, see https://laravel.com/docs/7.x/eloquent-mutators#defining-a-mutator
            if (isset($request->password) || !empty($request->password)) {
                $org_user->password = Hash::make($request->password);
            }

            $org_user->email      = $request->email;
            $org_user->first_name = $request->first_name ?? '';
            $org_user->last_name  = $request->last_name ?? '';

            $org_user->save();

            # Assign role
            $org_user->assignRole('organisation');

            return response()->json([
                'success' => true,
                'message' => __('messages.org_update_response'),
                'data' => $org
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response'),
                'data' => []
            ], 400);
        }
    }

    public function delete(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $organizations = Organisation::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.delete_successfully'),
                'data' => $organizations
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorize_response'),
            'data' => []
        ], 401);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            return (new OrganizationsExport($request->ids))->download('organisations.xlsx');
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }


    public function importOrganizations(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $import = new OrganizationImport;

            $file_name = $request->file('import_file')->getClientOriginalName();

            $path1 = $request->file('import_file')->store('temp');
            $path = storage_path('app') . '/' . $path1;

            Excel::import($import, $path);

            ImportLog::create(['from' => 'organization', 'file_name' => $file_name]);

            if (count($import->saves) === 0) {
                return response()->json([
                    'success' => false,
                    //'message' => __('messages.organization_import_failed'),
                    'message' => "Organization import was failed.",
                    'data' => $import->error
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.organization_import'),
                'data' => $import->saves,
                'errors' => $import->error,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();
        $org = Organisation::find($id);

        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $has_leads = LeadEscalation::where('organisation_id', $org->id)->exists();

            if ($has_leads) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.org_has_leads')
                ], 400);
            }

            $org->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.org_delete_response')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorize_response'),
                'data' => []
            ], 401);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function countries()
    {
        $countries = Country::all();

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    /**
     * Import from excel file .
     * column order: Org ID, Store, Add1, Suburb, State, postcode, Phone, LYSales, YTDSales, Pbook, Priority, Stock Kits
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $import = new OrganisationPostcodeImport;

            $file_name = $request->file('import_file')->getClientOriginalName();


            $path1 = $request->file('import_file')->store('temp');

            $path = storage_path('app') . '/' . $path1;

            Excel::import($import, $path);
            ImportLog::create(['from' => 'postcodes', 'file_name' => $file_name]);

            return response()->json([
                'success' => true,
                'message' => __('messages.organisation_postcode_import'),
                'data' => $import->error
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    /**
     * Get logs for postcode imports
     */
    public function organizationLogs(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $data = ImportLog::where('from', 'organization');

            $total = $data->count();

            $data = $data->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'message' => __('messages.logs_postcode'),
                'data' => ['data' => $data, 'total' => $total]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    /**
     * Get logs for postcode imports
     */
    public function logs(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $data = ImportLog::where('from', 'postcodes');

            $total = $data->count();

            $data = $data->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'message' => __('messages.logs_postcode'),
                'data' => ['data' => $data, 'total' => $total]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function deleteLogs(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            //ImportLog::where('from', 'postcodes')->whereIn('id', $request->ids)->delete();
            ImportLog::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.logs_deleted')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function comments($orgId)
    {
        $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
        $pageSize = isset($request->pageSize) ? $request->pageSize : 20;
        $comments = OrganizationComment::with('user')->where('organisation_id', $orgId)->orderBy('created_at', 'desc');

        $total = $comments->count();
        $data = $comments
            ->offset(($pageNo - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $data,
                'total' => 0
            ]
        ]);
    }

    public function saveOrgComment(Request $request, $orgId)
    {
        $user = auth()->user();

        $comment = OrganizationComment::where('id', $request->org_comment_id)->first();
        $data = [
            'user_id' => $user->id,
            'organisation_id' => $orgId,
            'comment' => $request->comment,
        ];

        if (isset($comment)) {
            $comment->update($data);
        } else {
            $comment = OrganizationComment::create($data);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Organisation comment was added successfully.',
            'data' => OrganizationComment::with('user')->where('id', $comment->id)->first()
        ]);
    }

    public function deleteOrgComment($id)
    {
        $deleteOrgComments = OrganizationComment::find($id);
        $deleteOrgComments->delete();

        return response()->json([
            'success' => true,
            'msg' =>  'Organisation comment was deleted successfully.',
            'data' => [],
        ]);
    }

    public function sendMessageOrgProfile(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'send_sms' => 'required|boolean',
            'send_email' => 'required|boolean',
        ]);

        $org = Organisation::find($id);
        $notif_added = false;

        if (!$org) {
            return response()->json([
                'success' => true,
                'message' => __('messages.org_not_found_response'),
                'data' => []
            ]);
        }

        // FOR SENT BY ICON - DON'T DELETE
        if ($request->send_email && $request->send_sms) {
            $email_and_sms = "both";

        } elseif ($request->send_email && !$request->send_sms) {
            $email_and_sms = "email";

        } elseif (!$request->send_email && $request->send_sms) {
            $email_and_sms = "sms";

        } else {
            $email_and_sms = "none";

        }

        if ($request->send_email && (!empty($request->email))) {
            # Send email to organisation
            Mail::to($request->email)->queue(new InquirerNotification('Leaf Stopper', $request->message));
        }

        if ($request->send_sms && (!empty($request->number))) {
            # Send sms to inquirer
            Notification::send_sms($request->number, $request->message);
        }

        $notif_structure = [
            'title' => "ORGANIZATION NOTIFICATIONS",
            'description' => $request->message,
            'metadata' => [
                'to' => $request->send_to,
                'organisation_id' => $id,
                'notification_type' => $email_and_sms,
                'email_and_sms' => $email_and_sms // FOR SENT BY ICON - DON'T DELETE
            ]
        ];

        $notification = Notification::create($notif_structure);

        //save all notification types
        $notification->logActivity(
            $org,
            $notif_structure
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.enquirer_message_sent'),
            'data' => []
        ]);
    }

    public function fetchMessageOrgProfile(Request $request, $id)
    {
        $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
        $pageSize = isset($request->pageSize) ? $request->pageSize : 20;

        $notifications = Notification::whereJsonContains('metadata->to', 'org_profile')->whereJsonContains('metadata->organisation_id', $id)->orderBy( 'id', 'DESC' );

        $total = $notifications->count();

        $data = $notifications
            ->offset(($pageNo - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        if ($total > 0) {
            return response()->json([
                'success' => true,
                'message' =>  __('organisation.has_records'),
                'data' => [
                    'notifications' => $data,
                    'total' => $total
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' =>  __('organisation.no_records'),
            'data' => [
                'notifications' => [],
                'total' => 0
            ]
        ]);
    }

    public function sendLeadUpdate($id){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $status = ['Pending', 'Declined-Lapsed', 'Awaiting Response', 'Awaiting Response - Reminder Sent', 'Awaiting Response - Admin Notified', 'Parked'];

            //has active leads
            $organisation = Organisation::whereHas('active_escalation', function($q) use($status){
                $q->whereIn('escalation_status', $status);
            })
            ->where('id', $id)
            ->whereJsonContains('metadata->manual_update', true)->orderBy('name', 'asc')->first();

            if($organisation){
                try{ $email = $organisation->organisation_users[0]->user->email; }
                catch(\Exception $ex){ $email = ''; }

                try{
                    if(! empty($email)){
                        Mail::to($email)->send(new OrganizationManualNotification($organisation));
                    }

                    return response()->json([
                        'success' => true,
                        'message' =>  'Manual Lead Update Sent.',
                    ]);
                }catch(\Exception $e){
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.general_error_response'),
                    ], 400);
                }
            }else{
                return response()->json([
                    'success' => false,
                    'message' =>   __('organisation.organisation_has_no_active_leads'),
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function leadAssigned(Request $request, $id){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
            $pageSize = isset($request->pageSize) ? $request->pageSize : 20;

            $leads = LeadEscalation::with(['lead.customer.address', 'lead.customer.address.country', 'lead.customer', 'lead'])
                ->where(['organisation_id' => $id, 'is_active' => 1])
                ->where('customer_type', 'Supply & Install')
                ->getSortedLeads();

            $total = $leads->count();

            $leads = $leads
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'message' =>  __('organisation.assigned_leads'),
                'data' => [
                    'leads' => $leads,
                    'total' => $total
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function postcodes($id){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $data = OrganizationPostcode::where('organisation_id', $id)->distinct('postcode')->get();

            if($data){
                return response()->json([
                    'success' => true,
                    'message' =>  __('organisation.postcodes'),
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => false,
                'message' =>   __('organisation.no_postcodes'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function updateStatus($org_id){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')){

            $organisation = Organisation::find($org_id);
            $suspension_type_is_system = (!isset($organisation->metadata['suspension_type']) || $organisation->metadata['suspension_type'] == false || $organisation->metadata['suspension_type'] == 'System') ? true : false;
            //if suspended by system
            if($suspension_type_is_system){
                if($organisation->has_critical){
                    $organisation->is_suspended = 1;
                }else{
                    $organisation->is_suspended = 0;
                }
                $organisation->save();
            }

            return response()->json([
                'success' => true,
                'data' => $organisation,
                'message' =>   __('organisation.status_update'),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function getReassignedLeads($id, Request $request){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')){

            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $reqData = ['organisation_id' => $id, 'reassinged' => true];

            $leads = LeadEscalation::with(['lead.customer.address', 'lead.customer.address.country', 'lead.customer', 'organisation', 'lead'])
                ->reassignedLeads($reqData)
                ->filterAsRole()
                ->getSortedLeads($reqData)
                ->active(0);

            # Get total leads
            $totalLeads = $leads->count();

            # Paginate leads
            $leads = $leads->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $leads,
                'message' => __('organisation.reassinged_leads'),
                'total' => $totalLeads,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }
}
