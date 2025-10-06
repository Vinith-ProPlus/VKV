<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MobileVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is already handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'current_version' => 'required|string|max:50',
            'new_version' => 'required|string|max:50',
            'android_link' => 'required|string|url|max:255',
            'ios_link' => 'required|string|url|max:255',
            'submit_text' => 'required|string|max:255',
            'ignore_text' => 'required|string|max:255',
            'update_type' => 'required|string|in:force,optional',
            'update_to' => 'required|string|in:android,ios,both',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'Update Title',
            'description' => 'Update Description',
            'current_version' => 'Current Version',
            'new_version' => 'New Version',
            'android_link' => 'Android App Link',
            'ios_link' => 'iOS App Link',
            'submit_text' => 'Submit Button Text',
            'ignore_text' => 'Ignore Button Text',
            'update_type' => 'Update Type',
            'update_to' => 'Update To Platform',
            'logo' => 'Update Image',
        ];
    }
}
