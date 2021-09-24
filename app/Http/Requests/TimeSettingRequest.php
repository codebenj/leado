<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            
            'start_date' => 'required_if:type,false',
            'start_time' => 'required_if:type,false',
            'stop_date' => 'required_if:type,false',
            'stop_time' => 'required_if:type,false',
            
            'start_day' => 'required_if:type,true',
            'stop_day' => 'required_if:type,true',
            'is_active' => 'required',
        ];
    }

    public function messages() 
    {
        return [
            'start_date.required_if' => 'Start date field is required',
            'start_time.required_if' => 'Start time field is required',
            'stop_date.required_if' => 'Stop date field is required',
            'stop_time.required_if' => 'Stop time field is required',

            'start_day.required_if' => 'Start day field is required',
            'stop_day.required_if' => 'Stop day field is required',
        ];
    }
}
