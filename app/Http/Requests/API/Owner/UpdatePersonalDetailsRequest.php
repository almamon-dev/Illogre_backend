<?php

namespace App\Http\Requests\API\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->user()->id,
            'phone_number' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:20048',
        ];
    }
}
