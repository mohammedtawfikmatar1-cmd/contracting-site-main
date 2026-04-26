<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // هذا الطلب يُستخدم داخل مسارات الإدارة المحمية بـ middleware('auth').
        return true;
    }

    public function rules(): array
    {
        $isMultilingual = is_array($this->input('title')) || is_array($this->input('content')) || is_array($this->input('category'));

        if ($isMultilingual) {
            return [
                'title.ar' => ['required', 'string', 'max:255'],
                'title.en' => ['nullable', 'string', 'max:255'],
                // المحتوى بدون حدود طول (تخزينه longtext) — لا نضع max هنا.
                'content.ar' => ['nullable', 'string'],
                'content.en' => ['nullable', 'string'],
                'category.ar' => ['nullable', 'string', 'max:255'],
                'category.en' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'max:4096'],
                'published_at' => ['nullable', 'date'],
            ];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
