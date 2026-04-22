<?php

namespace App\Http\Requests\API\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notify_new' => 'sometimes|boolean',
            'notify_ticket' => 'sometimes|boolean',
            'notify_invoice' => 'sometimes|boolean',
        ];
    }
}
