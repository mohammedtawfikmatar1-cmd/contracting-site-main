<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // النصوص الطويلة: لا نضع max هنا (الواجهة قد تضع maxlength كـ UX فقط).
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_published' => ['sometimes', 'boolean'],
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
            'logo' => ['required', 'image', 'max:4096'],
        ];
    }
}
