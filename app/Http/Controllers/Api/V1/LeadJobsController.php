<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\LeadJob;
use Illuminate\Http\Request;

class LeadJobsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')){
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;
            $lead_jobs = LeadJob::where('lead_id', $request->lead_id)
                ->where('comments', '!=', '')
                ->where('organisation_id', $user->organisation_user->organisation_id);

            $total = $lead_jobs->count();

            $lead_jobs = $lead_jobs->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            if($total > 0){
                return response()->json([
                    'success' => true,
                    'message' => __('messages.lead_job_found'),
                    'data' => [
                        'jobDetails' => $lead_jobs,
                        'total' => $total
                    ] ,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_job_not_found'),
                'data' => []
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')){
            $rules = [
                'lead_id' => 'required',
                'meters_gutter_edge' => 'required|numeric',
                'meters_valley' => 'required|numeric',
            ];

            $request->validate($rules);

            //add user organization login
            if(!isset($request->organisation_id)){
                $request->merge(['organisation_id' => $user->organisation_user->organisation_id]);
            }

            $lead_job = LeadJob::create($request->all());

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_job_create'),
                'data' => $lead_job
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')){
            $lead_job = LeadJob::find($id);

            if(!$lead_job){
                return response()->json([
                    'success' => true,
                    'message' => __('messages.lead_job_not_found'),
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_job_found'),
                'data' => $lead_job
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')){
            $lead_job = LeadJob::find($id);

            if(!$lead_job){
                return response()->json([
                    'success' => true,
                    'message' => __('messages.lead_job_not_found'),
                    'data' => []
                ]);
            }

            $lead_job->fill($request->all())->save();

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_job_updated'),
                'data' => $lead_job
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

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')){
            $lead_job = LeadJob::find($id);

            if(!$lead_job){
                return response()->json([
                    'success' => true,
                    'message' => __('messages.lead_job_not_found'),
                    'data' => []
                ]);
            }

            $lead_job->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_job_deleted'),
                'data' => $lead_job
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function getLeadJobsTotal($lead_id){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('organisation')){
            $lead_jobs = LeadJob::where('lead_id', $lead_id)->sum('sale');

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_job_total_sale'),
                'data' => $lead_jobs
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }
}
