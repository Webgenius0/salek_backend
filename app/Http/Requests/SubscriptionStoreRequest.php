<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubscriptionStoreRequest extends FormRequest
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
            'type'     => ['required', 'string', 'in:monthly,quarterly,annual'],
            'pay_type' => ['required', 'in:stripe,paypal']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
    */
    public function messages()
    {
        return [
            'type.required' => 'Subscription type is required',
            'type.string'   => 'Subscription type must be a string',
            'type.in'       => 'Subscription type must be one of the following: monthly, quarterly, annual',
            'pay_type.in'   => 'Payment type must be one of the following: stripe,paypal',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * This method is called when validation fails for the request. It throws an
     * HttpResponseException with a JSON response containing the validation errors,
     * a status flag set to false, and an HTTP status code of 422 (Unprocessable Entity).
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator The validator instance containing the validation errors.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
            'code'   => 422
        ],422));
    }
}
