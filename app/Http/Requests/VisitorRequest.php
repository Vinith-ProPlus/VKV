<?php

namespace App\Http\Requests;

use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class VisitorRequest extends FormRequest
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
            'customer_id' => 'required|string',
            'new_customer_name' => 'nullable|string|required_if:new_customer_flag,true',
            'new_customer_flag' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'site_id' => 'nullable|exists:sites,id',
            'status' => 'required|in:visited',
            'remarks' => 'nullable|string',
        ];
    }
}
