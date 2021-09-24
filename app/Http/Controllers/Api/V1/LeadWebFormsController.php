<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\LeadWebForm;
use Illuminate\Http\Request;
use Propaganistas\LaravelPhone\PhoneNumber;
class LeadWebFormsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $country_data = (new \League\ISO3166\ISO3166)->name('Australia');
        $mobile_number = PhoneNumber::make('09061554478', $country_data['alpha2']);
        return $mobile_number->formatE164();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $country = $request->country ?? 'Australia';
        $contact_number = $request->phone_number ?? '';

        $leadData = [
            'customer_type' => $request->lead_type ?? 'SUPPLY ONLY',
            'first_name' => $request->firstname ?? '',
            'last_name' => $request->lastname ?? '',
            'address' => $request->street ?? '', // get street
            'city' => $request->city ?? '',
            'suburb' => $request->suburb ?? '',
            'state' => $request->state ?? '',
            'postcode' => $request->postcode ?? '',
            'country' => $country,
            'email' => $request->email ?? '',
            'contact_number' => $contact_number, // phone_number
            'house_type' => $request->building_type ?? '', // building_type
            'roof_preference' => $request->roof ?? '', // roof
            'situation' => $request->situation ? $request->situation : ($request->position ? $request->position : ''),
            'gutter_edge_meters' => $request->meters_gutter_edge ?? '', // meters_gutter_edge
            'valley_meters' => $request->meters_valley ?? '', // meters_valley
            'comments' => ( $request->lead_type === 'General Enquiry' && $request->comments ) ? $request->comments : '',
            'source' => $request->hear_about_us ?? '',  // hear_about_us
            'commercial' => $request->commercial ?? '',  // commercial
            'metadata' => [
                'additional_information' => $request->comments ?? '',
                'landline_number' => $request->landline_number ?? '',
                'forminator_pro_form_id' => $request->forminator_pro_form_id ?? '',
                'address_search' => $request->address_search ?? '',
                'use_for_other' => $request->others_situation ? $request->others_situation : ( $request->position_others ? $request->position_others : ''), // others_situation
                'house_type_other' => $request->building_type_others ?? '', // building_type_others
                'roof_preference_other' => $request->roof_others ?? '', // roof_others
                'commercial_other' => $request->commercial_other ?? '',  // commercial_other
                'source_other' => $request->hear_about_others ?? '',  // hear_about_others
                'orginial_data' => json_encode($request->all()),
                'inquiry_type' => $request->inquiry_type ?? '',
                'attachments' => $request->attachments ?? ''
            ]
        ];
        if( $this->checkLeadIfExist($leadData) ){

            $lead = LeadWebForm::create($leadData);

            return response()->json([
                'success' => true,
                'message' => __('messages.lead_webform_created'),
                'data' => $leadData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.lead_webform_failed'),
            'data' => []
        ]);
    }

    public function checkLeadIfExist($leadData) {
        $lead = LeadWebForm::whereJsonContains('metadata->forminator_pro_form_id', $leadData['metadata']['forminator_pro_form_id'])->first();

        return $lead == null;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeadWebForm  $leadWebForm
     * @return \Illuminate\Http\Response
     */
    public function show(LeadWebForm $leadWebForm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeadWebForm  $leadWebForm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeadWebForm $leadWebForm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeadWebForm  $leadWebForm
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeadWebForm $leadWebForm)
    {
        //
    }
}
