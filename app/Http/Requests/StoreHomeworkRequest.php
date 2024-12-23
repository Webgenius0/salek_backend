<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHomeworkRequest extends FormRequest
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
            'course_id'     => 'nullable|integer|exists:courses,id',
            'chapter_id'    => 'nullable|integer|exists:chapters,id',
            'lesson_id'     => 'nullable|integer|exists:lessons,id',
            'title'         => 'required|string|max:255',
            'instruction'   => 'required|string',
            'file'          => 'nullable|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            'link'          => 'nullable|string|url|max:255',
            'deadline'      => 'nullable|date|after:now',
            'type'          => 'required|in:single,multiple',
            'question_type' => 'required|in:files,links',
        ];
    }

    /**
     * Custom message for validation
    */
    public function messages()
    {
        return [
            'course_id.required'     => 'Course id is required',
            'course_id.integer'      => 'Course id must be an integer',
            'chapter_id.required'    => 'Chapter id is required',
            'chapter_id.integer'     => 'Chapter id must be an integer',
            'lesson_id.required'     => 'Lesson id is required',
            'lesson_id.integer'      => 'Lesson id must be an integer',
            'title.required'         => 'Title is required',
            'title.string'           => 'Title must be a string',
            'title.max'              => 'Title must not be greater than 255 characters',
            'instruction.required'   => 'Instruction is required',
            'instruction.string'     => 'Instruction must be a string',
            'file.string'            => 'File must be a string',
            'file.max'               => 'File must not be greater than 255 characters',
            'link.string'            => 'Link must be a string',
            'link.max'               => 'Link must not be greater than 255 characters',
            'type.required'          => 'Type is required',
            'type.in'                => 'Type must be either single or multiple',
            'status.required'        => 'Status is required',
            'status.in'              => 'Status must be either active or inactive',
            'deadline.after'         => 'The deadline must be a date after the current time.',
            'question_type.required' => 'Question type is required',
            'question_type.in'       => 'Question type must be either files or links',
        ];
    }

    /**
     * failed validation message return function
     * laravel documentaion for more
     *
     * @param Validator $validator
     * @return mixed
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
            'code' => 422
        ], 422));
    }
}
