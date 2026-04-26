<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveAboutPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'about_title' => ['nullable', 'string', 'max:255'],
            // نصوص بدون حدود طول (لا نضع max حتى لا نقيّد المحتوى مستقبلاً).
            'about_text_1' => ['nullable', 'string'],
            'about_text_2' => ['nullable', 'string'],
            'about_main_image' => ['nullable', 'image', 'max:6144'],
        ];
    }
}
