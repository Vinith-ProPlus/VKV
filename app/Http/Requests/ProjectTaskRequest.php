<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteTaskRequest extends FormRequest
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

    public function rules()
    {
        return [
            'site_id' => 'required|exists:sites,id',
            'stage_id' => [
                'required',
                Rule::exists('site_stages', 'id')->where(function ($query) {
                    $query->where('site_id', $this->site_id);
                }),
            ],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('site_tasks')->ignore($this->route('site_task'))
            ],
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'status' => [
                'required', 'string',
                Rule::in(['Created', 'In-progress', 'On-hold', 'Completed', 'Deleted']),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
