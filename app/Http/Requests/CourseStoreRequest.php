<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CourseStoreRequest extends FormRequest
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
            'course.name'                      => ['required', 'string', 'min:2'],
            'course.description'               => ['required', 'string', 'min:2'],
            'course.total_class'               => ['required', 'integer'],
            'course.price'                     => ['required', 'integer'],
            'chapters'                         => ['required', 'array'],
            'chapters.*.chapter_name'          => ['required', 'string'],
            'chapters.*.lessons'               => ['required', 'array'],
            'chapters.*.lessons.*.lesson_name' => ['required', 'string'],
            'chapters.*.lessons.*.duration'    => ['required', 'string'],
            'chapters.*.lessons.*.image_url'   => ['nullable', 'image'],
            'chapters.*.lessons.*.video_url'   => ['nullable', 'mimes:mp4,avi,mkv'],
            'total_month'                      => ['required', 'integer'],
            'additional_charge'                => ['required', 'integer'],
        ];
    }

    public function messages()
    {
        return [
            'course.name.required' => 'The course name is required. Please provide a valid name for the course.',
            'course.name.string'   => 'The course name must be a valid string.',
            'course.name.min'      => 'The course name must be at least 2 characters long.',
            
            'course.description.required' => 'The course description is required. Please provide a brief description of the course.',
            'course.description.string'   => 'The course description must be a valid string.',
            'course.description.min'      => 'The course description must be at least 2 characters long.',
            
            'course.total_class.required' => 'The total number of classes is required. Please provide the number of classes in the course.',
            'course.total_class.integer'  => 'The total number of classes must be a valid integer.',
            
            'course.price.required' => 'The course price is required. Please provide the course price.',
            'course.price.integer'  => 'The course price must be a valid integer.',
            
            'chapters.required' => 'At least one chapter is required. Please add chapters to the course.',
            'chapters.array'    => 'The chapters must be an array of chapters.',
            
            'chapters.*.chapter_name.required' => 'The chapter name is required for each chapter.',
            'chapters.*.chapter_name.string'   => 'The chapter name must be a valid string.',
            
            'chapters.*.lessons.required' => 'Each chapter must have lessons. Please add at least one lesson to the chapter.',
            'chapters.*.lessons.array'    => 'Lessons must be an array for each chapter.',
            
            'chapters.*.lessons.*.lesson_name.required' => 'Each lesson must have a name. Please provide the name of the lesson.',
            'chapters.*.lessons.*.lesson_name.string'   => 'The lesson name must be a valid string.',
            
            'chapters.*.lessons.*.duration.required' => 'The duration of each lesson is required. Please provide the duration of the lesson.',
            'chapters.*.lessons.*.duration.string'   => 'The duration of the lesson must be a valid string.',
            
            'chapters.*.lessons.*.image_url.image'    => 'The image URL must be a valid image file.',
            'chapters.*.lessons.*.video_url.mimes'    => 'The video must be a valid MP4, AVI, or MKV file.',
            'chapters.*.lessons.*.video_url.nullable' => 'The video URL is optional, but must be a valid file if provided.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
            'code' => 422,
        ], 422));
    }
}
