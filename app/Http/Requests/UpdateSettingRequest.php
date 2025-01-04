<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSettingRequest extends FormRequest
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
            'project_name'     => ['required', 'string', 'min:2'],
            'project_logo'     => ['nullable', 'string', 'mimes:png,jpg,jpeg,webp'],
            'project_about'    => ['nullable', 'string', 'min:2'],
            'subscription_fee' => ['nullable', 'integer'],
            'project_switch'   => ['required', 'boolean'],
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
