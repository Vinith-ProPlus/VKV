<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PincodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string|array|string>
     */
    public function rules(): array
    {
        return [
            'pincode' => ['required', 'digits:6'],
            'area_id' => [
                'required', 'integer', 'exists:areas,id',
                Rule::unique('pincodes')->ignore($this->route('pincode')),
            ],
            'is_active' => 'required|boolean',
        ];
    }
}
