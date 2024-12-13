<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegistrationRequest extends FormRequest
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
            'name'          => ['required', 'string', 'min:2'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'confirm_email' => ['required', 'email', 'same:email'],
            'role'          => ['required', 'in:student,teacher,parent'],
            'password'      => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()]
        ];
    }

    public function messages()
    {
        return [
            'name.string'   => 'Name must be string',
            'confirm_email' => 'This email doesn"t match with email',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => true,
            'errors' => $validator->errors(),
            'code'   => 422
        ], 422));
    }
}
