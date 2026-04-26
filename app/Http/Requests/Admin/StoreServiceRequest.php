<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isMultilingual =
            is_array($this->input('title')) ||
            is_array($this->input('overview')) ||
            is_array($this->input('description'));

        if ($isMultilingual) {
            return [
                'title.ar' => ['required', 'string', 'max:255'],
                'title.en' => ['nullable', 'string', 'max:255'],
                'overview.ar' => ['nullable', 'string', 'max:500'],
                'overview.en' => ['nullable', 'string', 'max:500'],
                'description.ar' => ['nullable', 'string'],
                'description.en' => ['nullable', 'string'],
                'icon' => ['nullable', 'string', 'max:255'],
                'sort_order' => ['nullable', 'integer'],
                'image' => ['nullable', 'image', 'max:4096'],
            ];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
