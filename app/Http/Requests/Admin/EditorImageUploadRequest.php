<?php

/**
 * الغرض من الملف:
 * توحيد والتحكم في قواعد رفع وسائط محرر النصوص (Editor) عبر FormRequest.
 *
 * ملاحظة:
 * نسمح بالصور والفيديوهات الشائعة حتى لا يحفظ المحرر Base64 أو روابط خارجية داخل المحتوى.
 */
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EditorImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // المسار محمي داخل middleware('auth ') في routes/web.php
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'required_without:video', 'image', 'max:8192'],
            'video' => ['nullable', 'required_without:image', 'file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime', 'max:102400'],
            'context' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_-]+$/i'],
        ];
    }
}
