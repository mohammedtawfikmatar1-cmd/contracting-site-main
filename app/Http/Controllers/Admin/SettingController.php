<?php

/**
 * الغرض من الملف:
 * إدارة الإعدادات العامة وإعدادات الهوية البصرية للموقع.
 *
 * التبعية:
 * App\Http\Controllers\Admin\SettingController.
 *
 * المكونات الأساسية:
 * - CRUD للإعدادات العامة.
 * - حفظ إعدادات الشعار والألوان وبيانات الشركة وروابط الشبكات الاجتماعية.
 * - رفع الصور المرتبطة بالهوية البصرية.
 *
 * خريطة تدفق البيانات:
 * كل تعديل يتم هنا ينعكس مباشرة على الواجهة الأمامية،
 * مثل الشعار، الفافيكون، صور الأقسام، وألوان الثيم وبيانات التواصل.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * عرض جميع الإعدادات العامة.
     */
    public function index()
    {
        $settings = Setting::query()->orderBy('key')->paginate(20);

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * إنشاء إعداد جديد بمفتاح فريد.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('settings', 'key')],
            'value' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['text', 'longtext', 'image', 'color', 'json', 'boolean', 'integer'])],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['type'] === 'boolean') {
            // تخزين القيم المنطقية كنص متوافق مع بنية جدول settings.
            $validated['value'] = $request->boolean('value') ? '1' : '0';
        }

        Setting::create($validated);

        return redirect()->route('admin.settings.index')->with('success', 'تمت إضافة الإعداد بنجاح.');
    }

    /**
     * تحديث إعداد عام قائم.
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('settings', 'key')->ignore($setting->id)],
            'value' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['text', 'longtext', 'image', 'color', 'json', 'boolean', 'integer'])],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['type'] === 'boolean') {
            // توحيد تمثيل القيمة المنطقية في قاعدة البيانات.
            $validated['value'] = $request->boolean('value') ? '1' : '0';
        }

        $setting->update($validated);

        return redirect()->route('admin.settings.index')->with('success', 'تم تحديث الإعداد بنجاح.');
    }

    /**
     * حذف إعداد عام.
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();

        return redirect()->route('admin.settings.index')->with('success', 'تم حذف الإعداد بنجاح.');
    }

    /**
     * عرض شاشة الهوية البصرية والإعدادات المستخدمة مباشرة في الواجهة.
     */
    public function branding()
    {
        $settings = Setting::query()->get()->mapWithKeys(fn ($s) => [$s->key => $s->parseValue()]);
        $themePresets = $this->themePresetsForView();
        $structurePresets = $this->structurePresetsForView();

        return view('admin.settings.branding', compact('settings', 'themePresets', 'structurePresets'));
    }

    /**
     * عرض صفحة إعدادات "من نحن" المخصصة.
     * تمنح الإدارة تحكما مركزيا بمحتوى الصفحة (العنوان/النصوص/الصورة).
     */
    public function aboutPage()
    {
        $settings = Setting::query()->get()->mapWithKeys(fn ($s) => [$s->key => $s->parseValue()]);

        return view('admin.settings.about', compact('settings'));
    }

    /**
     * حفظ إعدادات صفحة "من نحن" من الشاشة المخصصة.
     * أي تعديل هنا ينعكس مباشرة في واجهة route('about').
     */
    public function saveAboutPage(Request $request)
    {
        $validated = $request->validate([
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_text_1' => ['nullable', 'string', 'max:3000'],
            'about_text_2' => ['nullable', 'string', 'max:3000'],
            'about_main_image' => ['nullable', 'image', 'max:6144'],
        ]);

        Setting::setValue('about_title', $validated['about_title'] ?? null, 'text');
        Setting::setValue('about_text_1', $validated['about_text_1'] ?? null, 'longtext');
        Setting::setValue('about_text_2', $validated['about_text_2'] ?? null, 'longtext');

        if ($request->hasFile('about_main_image')) {
            $existing = Setting::query()->where('key', 'about_main_image')->first();
            if ($existing?->value) {
                Storage::disk('public')->delete($existing->value);
            }

            $path = $request->file('about_main_image')->store('settings', 'public');
            Setting::setValue('about_main_image', $path, 'image');
        }

        return redirect()->route('admin.settings.about')->with('success', 'تم حفظ إعدادات صفحة من نحن بنجاح.');
    }

    /**
     * حفظ إعدادات الهوية البصرية.
     * هذا التدفق هو أهم نقطة ربط بين لوحة التحكم والعناصر الثابتة في الواجهة.
     */
    public function saveBranding(Request $request)
    {
        $validated = $request->validate([
            'theme_preset' => ['nullable', Rule::in(array_merge(['custom'], array_keys($this->themePresetsForView())))],
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
            'structure_preset' => ['nullable', Rule::in(array_merge(['custom'], array_keys($this->structurePresetsForView())))],

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

            'footer_brief' => ['nullable', 'string', 'max:2000'],
            'site_menu' => ['nullable', 'string', 'max:8000'],

            /* نصوص بانر الصفحة الرئيسية (تُعرض فوق صورة الهيرو في الواجهة الأمامية) */
            'home_hero_title' => ['nullable', 'string', 'max:500'],
            'home_hero_badge' => ['nullable', 'string', 'max:500'],
            'home_hero_description' => ['nullable', 'string', 'max:2000'],

            'logo_main' => ['nullable', 'image', 'max:4096'],
            'logo_transparent' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:2048'],
            'home_hero_image' => ['nullable', 'image', 'max:6144'],
            'about_main_image' => ['nullable', 'image', 'max:6144'],
            'footer_image' => ['nullable', 'image', 'max:6144'],
        ]);

        $themePreset = $validated['theme_preset'] ?? 'custom';
        Setting::setValue('theme_preset', $themePreset, 'text');

        $presets = $this->themePresetsForView();
        if ($themePreset !== 'custom' && isset($presets[$themePreset])) {
            $p = $presets[$themePreset];
            Setting::setValue('theme_primary_color', $p['primary'], 'color');
            Setting::setValue('theme_secondary_color', $p['secondary'], 'color');
            Setting::setValue('theme_accent_color', $p['accent'], 'color');
        } else {
            Setting::setValue('theme_primary_color', $validated['theme_primary_color'] ?? null, 'color');
            Setting::setValue('theme_secondary_color', $validated['theme_secondary_color'] ?? null, 'color');
            Setting::setValue('theme_accent_color', $validated['theme_accent_color'] ?? null, 'color');
        }

        $structurePreset = $validated['structure_preset'] ?? 'custom';
        Setting::setValue('structure_preset', $structurePreset, 'text');

        $structurePresets = $this->structurePresetsForView();
        if ($structurePreset !== 'custom' && isset($structurePresets[$structurePreset])) {
            $preset = $structurePresets[$structurePreset];
            Setting::setValue('body_bg_color', $preset['body_bg'], 'color');
            Setting::setValue('footer_bg_color', $preset['footer_bg'], 'color');
            Setting::setValue('header_bg_color', $preset['header_bg'], 'color');
            Setting::setValue('header_scrolled_bg_color', $preset['header_scrolled_bg'], 'color');
            Setting::setValue('header_text_color', $preset['header_text'], 'color');
            Setting::setValue('footer_text_color', $preset['footer_text'], 'color');
            Setting::setValue('content_text_color', $preset['content_text'], 'color');
        } else {
            Setting::setValue('body_bg_color', $validated['body_bg_color'] ?? null, 'color');
            Setting::setValue('footer_bg_color', $validated['footer_bg_color'] ?? null, 'color');
            Setting::setValue('header_bg_color', $validated['header_bg_color'] ?? null, 'color');
            Setting::setValue('header_scrolled_bg_color', $validated['header_scrolled_bg_color'] ?? null, 'color');
            Setting::setValue('header_text_color', $validated['header_text_color'] ?? null, 'color');
            Setting::setValue('footer_text_color', $validated['footer_text_color'] ?? null, 'color');
            Setting::setValue('content_text_color', $validated['content_text_color'] ?? null, 'color');
        }

        Setting::setValue('company_name', $validated['company_name'] ?? null, 'text');
        Setting::setValue('company_phone', trim((string) ($validated['company_phone'] ?? '')) ?: null, 'text');
        Setting::setValue('company_phone_2', trim((string) ($validated['company_phone_2'] ?? '')) ?: null, 'text');
        Setting::setValue('company_email', $validated['company_email'] ?? null, 'text');
        Setting::setValue('company_address', $validated['company_address'] ?? null, 'text');

        Setting::setValue('social_facebook', $validated['social_facebook'] ?? null, 'text');
        Setting::setValue('social_x', $validated['social_x'] ?? null, 'text');
        Setting::setValue('social_instagram', $validated['social_instagram'] ?? null, 'text');
        Setting::setValue('social_linkedin', $validated['social_linkedin'] ?? null, 'text');
        Setting::setValue('social_youtube', $validated['social_youtube'] ?? null, 'text');
        Setting::setValue('social_whatsapp', $validated['social_whatsapp'] ?? null, 'text');

        Setting::setValue('footer_brief', $validated['footer_brief'] ?? null, 'longtext');
        Setting::setValue('site_menu', $validated['site_menu'] ?? null, 'json');
        Setting::setValue('enable_multilingual', $request->boolean('enable_multilingual') ? '1' : '0', 'boolean');

        Setting::setValue('home_hero_title', $validated['home_hero_title'] ?? null, 'text');
        Setting::setValue('home_hero_badge', $validated['home_hero_badge'] ?? null, 'text');
        Setting::setValue('home_hero_description', $validated['home_hero_description'] ?? null, 'longtext');

        // معالجة ملفات الصور: حذف النسخة القديمة ثم حفظ النسخة الجديدة وتسجيل مسارها في settings.
        foreach (['logo_main', 'logo_transparent', 'favicon', 'home_hero_image', 'about_main_image', 'footer_image'] as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $existing = Setting::query()->where('key', $key)->first();
            if ($existing?->value) {
                Storage::disk('public')->delete($existing->value);
            }

            $path = $request->file($key)->store('settings', 'public');
            Setting::setValue($key, $path, 'image');
        }

        return redirect()->route('admin.settings.branding')->with('success', 'تم حفظ الإعدادات بنجاح.');
    }

    /**
     * ثلاث لوحات ألوان جاهزة للمقاولات؛ تُحفظ قيمها الفعلية في theme_* عند الاختيار.
     *
     * @return array<string, array{label: string, primary: string, secondary: string, accent: string}>
     */
    protected function themePresetsForView(): array
    {
        return [
            'preset_warm' => [
                'label' => 'كلاسيكي دافئ — برتقالي مقاولات مع أزرق ثبات وأخضر إنجاز',
                'primary' => '#ea580c',
                'secondary' => '#1e40af',
                'accent' => '#16a34a',
            ],
            'preset_marine' => [
                'label' => 'بحري احترافي — تركواز هادئ مع كحلي عميق ولمسة ذهبية للعناوين',
                'primary' => '#0f766e',
                'secondary' => '#0c4a6e',
                'accent' => '#d97706',
            ],
            'preset_mineral' => [
                'label' => 'معدني عصري — أزرق فولاذي مع رمادي أنيق وتركواز فاتح للإبراز',
                'primary' => '#2563eb',
                'secondary' => '#334155',
                'accent' => '#22d3ee',
            ],
        ];
    }

    /**
     * لوحات جاهزة لألوان الهيكل والنصوص لتسهيل الاختيار دون الحاجة لتنسيق يدوي.
     *
     * @return array<string, array{label: string, body_bg: string, footer_bg: string, header_bg: string, header_scrolled_bg: string, header_text: string, footer_text: string, content_text: string}>
     */
    protected function structurePresetsForView(): array
    {
        return [
            'structure_warm' => [
                'label' => 'متناسق مع الكلاسيكي الدافئ',
                'body_bg' => '#12151c',
                'footer_bg' => '#07080b',
                'header_bg' => '#1f2330',
                'header_scrolled_bg' => '#0d0f14',
                'header_text' => '#f8fafc',
                'footer_text' => '#f8fafc',
                'content_text' => '#f3f4f6',
            ],
            'structure_marine' => [
                'label' => 'متناسق مع البحري الاحترافي',
                'body_bg' => '#0f172a',
                'footer_bg' => '#082f49',
                'header_bg' => '#0c4a6e',
                'header_scrolled_bg' => '#082f49',
                'header_text' => '#ecfeff',
                'footer_text' => '#f0fdfa',
                'content_text' => '#e0f2fe',
            ],
            'structure_mineral' => [
                'label' => 'متناسق بدقة مع المعدني العصري',
                'body_bg' => '#334155',
                'footer_bg' => '#1e293b',
                'header_bg' => '#2563eb',
                'header_scrolled_bg' => '#1d4ed8',
                'header_text' => '#f8fafc',
                'footer_text' => '#e2e8f0',
                'content_text' => '#f8fafc',
            ],
        ];
    }
}
