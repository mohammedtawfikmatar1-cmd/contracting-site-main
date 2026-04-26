<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settingId = $this->route('setting')?->id;

        return [
            'key' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('settings', 'key')->ignore($settingId)],
            'value' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['text', 'longtext', 'image', 'color', 'json', 'boolean', 'integer'])],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
