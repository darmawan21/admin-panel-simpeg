<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::error('ClockInRequest validation failed', $validator->errors()->toArray());
        parent::failedValidation($validator);
    }

    public function rules(): array
    {
        return [
            // Either face_image (base64 JPEG) or embedding (192-dim array) is required
            'face_image' => ['required_without:embedding', 'nullable', 'string'],
            'embedding' => ['required_without:face_image', 'nullable', 'array', 'min:192', 'max:192'],
            'embedding.*' => ['numeric'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'timestamp' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'face_image.required_without' => 'Either a face image or embedding is required.',
            'embedding.required_without' => 'Either an embedding or face image is required.',
            'embedding.min' => 'Embedding must be a 192-dimensional vector.',
            'embedding.max' => 'Embedding must be a 192-dimensional vector.',
            'latitude.required' => 'GPS latitude is required.',
            'longitude.required' => 'GPS longitude is required.',
            'accuracy.required' => 'GPS accuracy is required.',
            'timestamp.required' => 'Device timestamp is required.',
        ];
    }
}
