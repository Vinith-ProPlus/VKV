<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteRequest extends FormRequest
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
            'site_no' => [
                'required', 'string', 'max:100',
                Rule::unique('sites')->ignore($this->route('site'))->where(function ($query) {
                    return $query->where('project_id', $this->input('project_id'));
                })
            ],
            'project_id'=>'required|integer|exists:projects,id',
            'type'=>'required|string|max:255',
            'range'=>'required|string|max:255',
            'engineer_id'=>'required|integer|exists:users,id',
            'area_sqft'=>'required',
            'status' => ['required', 'string', Rule::in(SITE_STATUSES)],
            'stages' => 'nullable|array',
            'stages.*.name' => 'required|string',
            'sold_amount' => [
                Rule::requiredIf($this->input('status') === COMPLETED), 'numeric','regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }
}
