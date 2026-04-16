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
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'state_id' => 'nullable|exists:states,id',
            'district_id' => 'nullable|exists:districts,id',
            'area_id' => 'nullable|exists:areas,id',
            'pincode_id' => 'nullable|exists:pincodes,id',
            'email' => 'nullable|email',
            'mobile_number' => [
                'required',
                'digits_between:7,12',
                Rule::unique('leads')->ignore($this->route('lead'))
            ],
            'lead_source_id' => 'nullable|exists:lead_sources,id',
        ];
    }
}
