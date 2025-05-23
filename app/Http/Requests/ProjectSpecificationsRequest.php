<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectSpecificationsRequest extends FormRequest
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
            'spec_name' => [
                'required', 'string', 'max:100',
                Rule::unique('project_specifications')->ignore($this->route('project_specification'))
            ],
            'is_active' => 'required|boolean',
            'spec_values' => 'required',
        ];
    }
}
