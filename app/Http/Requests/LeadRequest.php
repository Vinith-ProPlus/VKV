<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'lead_title' => [
                'required', 'string', 'max:100',
                Rule::unique('leads')->ignore($this->route('lead'))
            ],
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'city_id' => 'required|exists:cities,id',
            'pincode_id' => 'required|exists:pincodes,id',
            'gst_number' => 'nullable|string|max:18',
            'email' => 'nullable|email',
            'mobile_number' => 'required|digits_between:7,12',
            'whatsapp_number' => 'required|digits_between:7,12',
            'lead_source_id' => 'required|exists:lead_sources,id',
            'lead_status_id' => 'required|exists:lead_statuses,id',
            'lead_owner_id' => 'required|exists:users,id',
            'lead_follow_by_id' => 'required|exists:users,id'
        ];
    }
}
