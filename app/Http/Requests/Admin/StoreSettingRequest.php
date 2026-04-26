<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('settings', 'key')],
            'value' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['text', 'longtext', 'image', 'color', 'json', 'boolean', 'integer'])],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
