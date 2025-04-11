<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    { 

        return [
            'site_id' => 'required|integer|exists:sites,id',
            'project_id' => [
                'required', 'string', 'max:100',
                Rule::unique('projects')->ignore($this->route('project'))
            ],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('projects')->ignore($this->route('project'))
            ],
            'location'=>'required|string|max:255',
            'type'=>'required|string|max:255',
            'units'=>'required|integer',
            'target_customers'=>'required|string|max:255',
            'range'=>'required|string|max:255',
            'engineer_id'=>'required|integer|exists:users,id',
            'area_sqft'=>'required',
            'status' => ['required', 'string', Rule::in(PROJECT_STATUSES)],
            'stages' => 'nullable|array',
            'stages.*.name' => 'required|string',
            'sold_amount' => [
                Rule::requiredIf($this->input('status') === COMPLETED), 'numeric','regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }
}
