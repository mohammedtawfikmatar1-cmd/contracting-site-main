<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_preset' => ['nullable', 'string', 'max:64'],
            'theme_primary_color' => ['nullable', 'string', 'max:32'],
            'theme_secondary_color' => ['nullable', 'string', 'max:32'],
            'theme_accent_color' => ['nullable', 'string', 'max:32'],
            'body_bg_color' => ['nullable', 'string', 'max:32'],
            'footer_bg_color' => ['nullable', 'string', 'max:32'],
            'header_bg_color' => ['nullable', 'string', 'max:32'],
            'header_scrolled_bg_color' => ['nullable', 'string', 'max:32'],
            'header_text_color' => ['nullable', 'string', 'max:32'],
            'footer_text_color' => ['nullable', 'string', 'max:32'],
            'content_text_color' => ['nullable', 'string', 'max:32'],
            'structure_preset' => ['nullable', 'string', 'max:64'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_phone_2' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],

            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_x' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_linkedin' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
            'social_whatsapp' => ['nullable', 'string', 'max:255'],

            // نصوص طويلة: لا نضع max هنا لتجنب تقييد المحتوى لاحقاً.
            'footer_brief' => ['nullable', 'string'],
            'site_menu' => ['nullable', 'string'],

            'home_hero_title' => ['nullable', 'string'],
            'home_hero_badge' => ['nullable', 'string'],
            'home_hero_description' => ['nullable', 'string'],

            'logo_main' => ['nullable', 'image', 'max:4096'],
            'logo_transparent' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:2048'],
            'home_hero_image' => ['nullable', 'image', 'max:6144'],
            'about_main_image' => ['nullable', 'image', 'max:6144'],
            'footer_image' => ['nullable', 'image', 'max:6144'],
        ];
    }
}
