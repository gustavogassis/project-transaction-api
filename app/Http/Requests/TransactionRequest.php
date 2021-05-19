<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value' => 'required|numeric',
            'payer' => 'required|numeric',
            'payee' => 'required|numeric'
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'A value is required',
            'value.numeric'  => 'The value must be numeric'
        ];
    }
}
