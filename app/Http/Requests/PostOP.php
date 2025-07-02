<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostOP extends FormRequest
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
    public function rules()
    {
        return [
            'owner_name' => 'required|string',
            'owner_address' => 'required|string',
            'account_code_details' => 'required|array',
            'account_code_details.*.code' => 'required|string',
            'account_code_details.*.amount' => 'required|numeric',
            'livestock' => 'required|array',
            'livestock.*.id' => 'required|integer',
            'purpose' => 'required|string'
        ];
    }
}
