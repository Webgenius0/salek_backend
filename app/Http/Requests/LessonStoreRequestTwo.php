<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class LessonStoreRequestTwo extends FormRequest
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
            'course_id'  => ['required', 'integer', 'exists:courses,id'],
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'name'       => [
                'required',
                'string',
                'min:2',
                Rule::unique('lessons')->where(function ($query) {
                    return $query->where('course_id', $this->input('course_id'))
                    ->where('chapter_id', $this->input('chapter_id'));
                }),
            ],
            'video_url'  => ['nullable', 'mimes:mp4,mov,ogg,qt', 'max:6000'],
            'duration'   => ['nullable', 'integer'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
            'code'    => 422,
        ], 422));
    }
}
