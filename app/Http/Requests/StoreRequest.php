<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
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
            'name'              => 'required',
            'street_address'    => 'required',
            'suburb'            => 'required',
            'postcode'          => 'required',
            'phone_number'      => 'required',
            'code'              => 'required|unique:stores,code,'.$this->id,
        ];
    }
}
