<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\ReportAdvertisingMediumBreakdownExport;
use App\Exports\ReportOrganisationStatusBreakdownExport;
use App\Exports\ReportLeadWonBreakdownExport;
use App\Http\Controllers\Controller;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Carbon\Carbon;
class ReportController extends Controller
{
    public function __construct()
    {
        $this->user = auth()->user();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function mediumBreakdown(Request $request)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $data = LeadEscalation::getReportMediumBreakDown($request->all())->get();

            $states = Lead::getCustomerStates();

            return response()->json([
                'success' => true,
                'message' => __('messages.report_medium_breakdown'),
                'data' => $data,
                'states' => $states,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
            'data' => []
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function organisationBreakdown(Request $request)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){

            $data = LeadEscalation::getReportOrganisationBreakDown($request->all())->get()
                ->makeHidden(['max_extension', 'min_extension', 'time_left']);

            //$data = Lead::getReportOrganisationBreakDown($request->all())->get();

            if ($request['type']) {
                $org_query = Organisation::join('users','organisations.user_id', '=', 'users.id')
                ->whereIn( 'organisations.id', $request['ids'] )->get();

                $new_data = collect( $data );
                $new_orgs = collect( $org_query );
                $new_data_orgs = $new_orgs->merge( $new_data );
            } else {
                $new_data_orgs = $data;
            }

            $states = LeadEscalation::getOrganisationStates();

            $now = Carbon::now();
            $months = [ $now->format( 'M' ), $now->firstOfMonth()->subMonthsNoOverflow( 1 )->format( 'M' ), $now->firstOfMonth()->subMonthsNoOverflow( 1 )->format( 'M' ) ];

            return response()->json([
                'success' => true,
                'message' => __('messages.report_organisation_status_breakdown'),
                'data' => $new_data_orgs,
                'states' => $states,
                'months' => $months
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
            'data' => []
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function leadsWonBreakdown(Request $request){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            //$data = LeadEscalation::getLeadsWonBreakdown($request->all());
            $data = Lead::getLeadsWonBreakdown($request->all());

            $states = LeadEscalation::getOrganisationStates();

            return response()->json([
                'success' => true,
                'message' => __('messages.leads_won_breakdown'),
                'data' => $data,
                'states' => $states
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
            'data' => [],
        ]);
    }

    public function leadStats($lead_id){
        $data = LeadEscalation::getLeadStats($lead_id);

        return response()->json([
            'success' => true,
            'message' => __('messages.leads_won_breakdown'),
            'data' => $data
        ]);
    }

    public function organizationStats($org_id){
        $data = LeadEscalation::getOrganizationStats($org_id);

        return response()->json([
            'success' => true,
            'message' => __('messages.leads_won_breakdown'),
            'data' => $data
        ]);
    }

    private function showUniqueAndCount($array){
        $array = array_count_values($array);
        $text = '';
        foreach($array as $key => $value){
            $text .= $key.'('.$value.'), ';
        }
        return substr($text, 0, (strlen($text)-2));
    }

    public function exportAdvertisingMediumBreakdown(Request $request){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $data = Lead::getReportMediumBreakDown($request->all())->get();
            //show unique and count
            foreach($data as $row){
                $array_states = explode(', ', $row->states);
                $row->states = $this->showUniqueAndCount($array_states);
            }
            if($request->export == 'excel'){
                return new ReportAdvertisingMediumBreakdownExport($data);
            }else{
                $pdf = PDF::loadView('export.pdf.advertising-medium-breakdown', ['data' => $data]);
                return $pdf->download('advertising-medium-breakdown.pdf');
            }
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function exportOrganisationStatusBreakdown(Request $request){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $data = LeadEscalation::getReportOrganisationBreakDown($request->all())->get();
            if($request->export == 'excel'){
                return new ReportOrganisationStatusBreakdownExport($data);
            }else{
                $pdf = PDF::loadView('export.pdf.organisation-status-breakdown', ['data' => $data]);
                return $pdf->download('organisation-status-breakdown.pdf');
            }
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function exportLeadWonBreakdown(Request $request){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $data = LeadEscalation::getLeadsWonBreakdown($request->all());
            if($request->export == 'excel'){
                return new ReportLeadWonBreakdownExport($data);
            }else{
                $pdf = PDF::loadView('export.pdf.lead-won-breakdown', ['data' => $data]);
                return $pdf->download('lead-won-breakdown.pdf');
            }
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }
}
