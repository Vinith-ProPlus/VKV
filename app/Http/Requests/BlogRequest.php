<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks'      => 'required|string|max:500',
            'project_id' => 'required|exists:projects,id',
            'stage_ids' => 'required',
        ];
    }
}
