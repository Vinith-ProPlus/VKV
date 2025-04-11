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
            'name' => 'required|string|max:255',
            'mobile' => [
                'required',
                'digits_between:7,12',
                static function ($attribute, $value, $fail) {
                    $exists = Visitor::whereMobile($value)
                        ->whereProjectId(request('project_id'))
                        ->whereDate('created_at', Carbon::today())
                        ->exists();

                    if ($exists) {
                        $fail('The mobile number has already been registered for this project today.');
                    }
                },
            ],
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
            'project_id' => 'required|exists:projects,id'
        ];
    }
}
