<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('warehouses')->ignore($this->route('warehouse'))
            ],
            'address' => 'required|string|max:500',
            'state_id' => 'required|integer|exists:states,id',
            'district_id' => 'required|integer|exists:districts,id',
            'city_id' => 'required|integer|exists:cities,id',
            'pincode_id' => 'required|integer|exists:pincodes,id',
            'is_active' => 'required|boolean',
        ];
    }
}
