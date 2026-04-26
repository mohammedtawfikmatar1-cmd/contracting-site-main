<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isMultilingual = is_array($this->input('title')) || is_array($this->input('content'));

        if ($isMultilingual) {
            return [
                'title.ar' => ['required', 'string', 'max:255'],
                'title.en' => ['nullable', 'string', 'max:255'],
                // محتوى الصفحة يُعتبر longtext ولا نضع له max.
                'content.ar' => ['nullable', 'string'],
                'content.en' => ['nullable', 'string'],
            ];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ];
    }
}
