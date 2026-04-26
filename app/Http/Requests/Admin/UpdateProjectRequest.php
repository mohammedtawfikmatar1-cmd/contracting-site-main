<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isMultilingual =
            is_array($this->input('title')) ||
            is_array($this->input('description')) ||
            is_array($this->input('category')) ||
            is_array($this->input('location'));

        if ($isMultilingual) {
            return [
                'service_id' => ['required', 'exists:services,id'],
                'client_id' => ['nullable', 'integer'],
                'title.ar' => ['required', 'string', 'max:255'],
                'title.en' => ['nullable', 'string', 'max:255'],
                'description.ar' => ['nullable', 'string'],
                'description.en' => ['nullable', 'string'],
                'category.ar' => ['nullable', 'string', 'max:255'],
                'category.en' => ['nullable', 'string', 'max:255'],
                'location.ar' => ['nullable', 'string', 'max:255'],
                'location.en' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'max:4096'],
            ];
        }

        return [
            'service_id' => ['required', 'exists:services,id'],
            'client_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
