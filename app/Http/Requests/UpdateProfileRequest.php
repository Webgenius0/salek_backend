<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
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
            'name'         => ['required', 'string', 'min:2'],
            'dob'          => ['nullable'],
            'email'        => ['required', 'email', Rule::unique('users')->ignore($this->user()->id)],
            'mobile_phone' => ['required', 'string', 'max:15'],
            'gender'       => ['required', 'in:male,female,custom'],
            'class_no'     => ['nullable', 'integer'],
            'class_name'   => ['nullable', 'string'],
            'avatar'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5140']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
            'code'   => 422
        ], 422));
    }
}
