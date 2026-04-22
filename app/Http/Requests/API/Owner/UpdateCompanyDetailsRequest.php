<?php

namespace App\Http\Requests\API\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'vat_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ];
    }
}
