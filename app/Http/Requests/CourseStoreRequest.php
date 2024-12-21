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
            'name'               => ['required', 'string', 'min:2'],
            'description'        => ['required', 'string', 'min:2'],
            'total_class'        => ['required', 'integer'],
            'price'              => ['required', 'integer'],
            'total_month'        => ['required', 'integer'],
            'additional_charge'  => ['required', 'integer'],
            'introduction_title' => ['required', 'string', 'min:2'],
            'cover_photo'        => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'class_video'        => ['required', 'file', 'mimes:mp4,mov,ogg,qt', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The course name is required. Please provide a valid name for the course.',
            'name.string'   => 'The course name must be a valid string.',
            'name.min'      => 'The course name must be at least 2 characters long.',
            
            'description.required' => 'The course description is required. Please provide a brief description of the course.',
            'description.string'   => 'The course description must be a valid string.',
            'description.min'      => 'The course description must be at least 2 characters long.',
            
            'total_class.required' => 'The total number of classes is required. Please provide the number of classes in the course.',
            'total_class.integer'  => 'The total number of classes must be a valid integer.',
            
            'price.required' => 'The course price is required. Please provide the course price.',
            'price.integer'  => 'The course price must be a valid integer.',
            
            'introduction_title' => 'The introduction title is required. Please provide a title for the introduction.',
            'cover_photo'        => 'The cover photo is required. Please upload a cover photo for the course.',
            'class_video'        => 'The class video is required. Please upload a video for the course.',
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
