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
            'pincode' => [
                'required', 'string', 'max:100',
                Rule::unique('pincodes')->ignore($this->route('pincode'))
            ],
            'city_id' => [
                'required', 'integer', 'exists:cities,id',
            ],
            'is_active' => 'required|boolean',
        ];
    }
}
