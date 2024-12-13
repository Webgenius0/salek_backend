<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePasswordRequest extends FormRequest
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
            'email'        => ['required', 'email', 'exists:users,email'],
            'otp'          => ['required', 'string'],
            'new_password' => ['required', 'string',
            Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(), 'same:confirm_password'],
            'confirm_password' => ['required', 'string', 'min:4']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errros' => $validator->errors(),
            'code'   => 422,
        ], 422));
    }
}
