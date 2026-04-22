<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['required', Rule::in(['owner', 'super_admin'])],
            'terms' => ['required', 'accepted'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must accept the terms and conditions.',
            'company_name.required' => 'Company name is mandatory.',
        ];
    }
}
