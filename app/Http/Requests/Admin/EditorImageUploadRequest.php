<?php

/**
 * الغرض من الملف:
 * توحيد والتحكم في قواعد رفع صور محرر النصوص (Editor) عبر FormRequest.
 *
 * ملاحظة:
 * نسمح فقط بأنواع الصور الشائعة وبحجم مناسب للويب حتى لا يتحول التخزين العام إلى مستودع ضخم.
 */
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EditorImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // المسار محمي داخل middleware('auth') في routes/web.php
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:4096'],
            'context' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_-]+$/i'],
        ];
    }
}

