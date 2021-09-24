<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OrganizationRequest extends FormRequest
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
            'name'           => 'required|string',
            // 'city'           => 'required|string',
            'state'          => 'required|string',
            'org_code'       => 'required|string',
            'contact_number' => 'required|string',
            'email'          => 'required|string|unique:users,email',
            'password'       => 'required|string',
        ];
    }
}
