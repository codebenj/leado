<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LeadRequest extends FormRequest
{
        /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasRole('administrator') || Auth::user()->hasRole('super admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_type'         => 'required|string',
            //'cef_id'                => 'required|string',
            'escalation_level'      => 'required|string',
            //'escalation_status'     => 'required_if:customer_type,Supply & Install',
            'escalation_status'     => 'required|string',
            'email'                 => 'required_if:customer_type,Supply Only',
            'first_name'            => 'required|string',
            'last_name'             => 'required_if:customer_type,Supply Only,Supply & Install',
            'contact_number'        => 'required_if:customer_type,Supply & Install',
            'country'               => 'required|string',
        ];
    }
}
