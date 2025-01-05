<?php

namespace App\Http\Requests;

use App\Rules\IsTeacher;
use App\Rules\ValidTimeRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTeacherRequest extends FormRequest
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
            'teacher_id'   => ['required', 'exists:users,id', new IsTeacher],
            'time_range'   => ['required', 'string', new ValidTimeRange],
            'booking_date' => ['required', 'date', 'after_or_equal:today']
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * This method is triggered when validation fails for a request. It throws an
     * HttpResponseException with a JSON response containing the validation errors,
     * a status flag, and an HTTP status code of 422 (Unprocessable Entity).
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *     The validator instance containing the validation errors.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     *     Thrown to indicate that the request validation has failed.
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => true,
            'errors' => $validator->errors(),
            'code'   => 422,
        ], 422));
    }
}
