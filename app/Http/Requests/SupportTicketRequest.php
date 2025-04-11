<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject'      => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'support_type' => 'required|exists:support_types,id',
            'message' => 'required|string|max:255',
            'status' => ['required', 'string', Rule::in(SUPPORT_TICKET_STATUSES)],
        ];
    }
}
