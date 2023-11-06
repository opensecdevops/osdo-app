<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackage extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'package' => 'required|file|mimes:zip|max:10000',
            'version' => 'regex:/[a-zA-Z]?\d{1,2}\.\d{1,2}\.\d{1,3}/s'
        ];
    }

    public function messages(): array
    {
        return [
            'version.regex' => 'The versión format is invalid XX.XX.XX',
        ];
    }
}
