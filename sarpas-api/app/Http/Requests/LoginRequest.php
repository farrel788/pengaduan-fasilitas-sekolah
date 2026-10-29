<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [
        'nisn'     => ['required_without:email', 'nullable', 'string', 'digits:10'],
        'email'    => ['required_without:nisn', 'nullable', 'email'],
        'password' => ['required', 'string'],
    ];
}
}
