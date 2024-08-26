<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditAdminRequest extends FormRequest
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
            'username' => 'nullable|string|max:255|required_without_all:email,phone,password',
            'email' => 'nullable|string|email|max:255|required_without_all:username,phone,password',
            'phone' => 'nullable|string|max:255|required_without_all:username,email,password',
            'password' => 'nullable|string|min:8|required_without_all:username,email,phone',
        ];
    }
}
