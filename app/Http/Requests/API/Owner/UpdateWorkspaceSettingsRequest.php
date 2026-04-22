<?php

namespace App\Http\Requests\API\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'support_email' => 'nullable|email|max:255',
            'website_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:20048',
        ];

    }
}
