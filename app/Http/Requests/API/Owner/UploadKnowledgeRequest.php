<?php

namespace App\Http\Requests\API\Owner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadKnowledgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required_if:type,file|file|mimes:pdf,docx,txt|max:10240',
            'url' => 'required_if:type,url|url',
            'text' => 'required_if:type,text|string',
            'type' => 'required|in:file,url,text',
            'name' => 'nullable|string|max:255',
        ];
    }
}
