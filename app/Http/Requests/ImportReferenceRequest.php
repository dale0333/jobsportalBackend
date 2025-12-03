<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportReferenceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'The file must be a valid Excel or CSV file.',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }
}
