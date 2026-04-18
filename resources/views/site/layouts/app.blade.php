<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <!--
        الغرض من الملف:
        القالب الأساسي للواجهة الأمامية (Layout) الذي يحدد الهيكل العام للصفحات ويحقن الأقسام المتغيرة عبر توجيهات Blade الخاصة بالأقسام.

        خريطة تدفق البيانات:
        - $siteSettings: قادم من إعدادات لوحة التحكم (قسم إعدادات/الهوية البصرية) ويُستخدم للشعار والألوان والفافيكون وغيرها.
        - تُحمَّل متغيرات الألوان الديناميكية بعد ملفات CSS العامة حتى تطغى على قيم :root الثابتة في global.css.
        - $siteMenu: قادم من إعدادات لوحة التحكم (site_menu) ويُستخدم لبناء روابط التنقل في الهيدر والفوتر.
        - قسم المحتوى الرئيسي: يمثل الصفحة الفعلية التي تعتمد بياناتها على المتحكمات (SiteController).
    -->
    @php
        $siteSettings = $siteSettings ?? collect();
        $isAdminPreview = auth()->check();

        $settingsValue = function (array|string $keys, string $adminFallback = '', string $publicFallback = '') use ($siteSettings, $isAdminPreview) {
            $keys = is_array($keys) ? $keys : [$keys];
            foreach ($keys as $key) {
                $value = $siteSettings[$key] ?? null;
                if (filled($value)) {
                    return $value;
                }
            }

            return $isAdminPreview ? $adminFallback : $publicFallback;
        };

        $settingsMedia = function (array|string $keys, ?string $adminFallback = null, ?string $publicFallback = null) use ($siteSettings, $isAdminPreview) {
            $keys = is_array($keys) ? $keys : [$keys];
            foreach ($keys as $key) {
                $value = $siteSettings[$key] ?? null;
                if (filled($value)) {
                    return $value;
                }
            }

            return $isAdminPreview ? $adminFallback : $publicFallback;
        };

        // مفاتيح أساسية يُفضّل توفرها لإظهار الموقع بشكل مكتمل (الهوية البصرية + بيانات التواصل).
        $brandingRequiredKeys = [
            'company_name',
            'company_phone',
            'company_email',
            'company_address',
            'logo_main',
        ];

        $missingBrandingKeys = collect($brandingRequiredKeys)
            ->filter(fn ($key) => empty($siteSettings[$key] ?? null))
            ->values();
    @endphp

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('description', 'شركة مقاولات متخصصة في تنفيذ مشاريع البناء والتشييد بجودة عالية.') " />
    <title>@yield('title', 'شركة مقاولات - الموقع الرسمي')</title>
    <link rel="icon" href="{{ $settingsMedia('favicon', asset('favicon.ico'), asset('favicon.ico')) }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
    <!-- Styles: ملفات Vite أولاً ثم متغيرات الثيم حتى لا تعيد global.css تعريف :root إلى ألوان ثابتة. -->
    @vite(['resources/css/global.css', 'resources/css/header.css', 'resources/css/footer.css'])
    @php
        /*
         * قراءة الألوان مباشرة من إعدادات الموقع (بعد parseValue) لضمان تطابق القيم المحفوظة.
         */
        $themePrimary = $siteSettings['theme_primary_color'] ?? null;
        $themeSecondary = $siteSettings['theme_secondary_color'] ?? null;
        $themeAccent = $siteSettings['theme_accent_color'] ?? null;
        if (! is_string($themePrimary) || ! str_starts_with(trim($themePrimary), '#')) {
            $themePrimary = '#ff7a1a';
        }
        if (! is_string($themeSecondary) || ! str_starts_with(trim($themeSecondary), '#')) {
            $themeSecondary = '#0b5ed7';
        }
        if (! is_string($themeAccent) || ! str_starts_with(trim($themeAccent), '#')) {
            $themeAccent = '#22c55e';
        }
    @endphp
    <style>
        :root {
            --theme-primary: {{ $themePrimary }};
            --theme-secondary: {{ $themeSecondary }};
            --theme-accent: {{ $themeAccent }};

            /* ربط رموز التصميم الحالية باللون الرئيسي من لوحة التحكم */
            --orange: var(--theme-primary);
            --orange-light: color-mix(in srgb, var(--theme-primary) 78%, white);
            --orange-dark: color-mix(in srgb, var(--theme-primary) 78%, black);
            --orange-glow: color-mix(in srgb, var(--theme-primary) 22%, transparent);
            --orange-subtle: color-mix(in srgb, var(--theme-primary) 10%, transparent);
        }

        .admin-preview-toolbar {
            background: linear-gradient(90deg, rgba(14, 24, 45, 0.96), rgba(16, 36, 62, 0.96));
            color: #e5edf8;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            position: sticky;
            top: 0;
            z-index: 1200;
            backdrop-filter: blur(8px);
        }

        .admin-preview-toolbar .toolbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            font-size: 13px;
        }

        .admin-preview-toolbar .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .admin-preview-toolbar a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            padding: 6px 10px;
            background: rgba(255, 255, 255, 0.08);
        }

        .admin-preview-toolbar a:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .admin-preview-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 6px 10px;
            background: rgba(255, 193, 7, 0.18);
            color: #ffdd8a;
            border: 1px solid rgba(255, 193, 7, 0.35);
            font-weight: 800;
        }

        /*
         * عند تفعيل شريط المعاينة الإداري (للمسجل دخول)،
         * نُزاح الهيدر الثابت للأسفل حتى لا يختفي خلف الشريط.
         */
        body.has-admin-toolbar .site-header {
            top: var(--admin-toolbar-offset, 48px);
        }
    </style>
    @yield('styles')
</head>
<body class="{{ $isAdminPreview ? 'has-admin-toolbar' : '' }}" style="{{ $isAdminPreview ? '--admin-toolbar-offset: 48px;' : '' }}">
    <a class="skip-link" href="#home">تخطي إلى المحتوى</a>

    @if($isAdminPreview)
        <div class="admin-preview-toolbar">
            <div class="container toolbar-inner">
                <div class="admin-preview-chip">وضع المعاينة الإدارية</div>
                <div class="toolbar-actions">
                    <a href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
                    <a href="{{ route('admin.settings.branding') }}">الهوية البصرية</a>
                    <a href="{{ route('admin.settings.index') }}">الإعدادات</a>
                </div>
            </div>
        </div>
    @endif

    <!-- Ambient orbs -->
    <div class="bg-orbs" aria-hidden="true">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- ═══ HEADER (يعتمد على $siteSettings و $siteMenu من لوحة التحكم) ═══ -->
    @include('site.partials.header')

    <main>
        @if($isAdminPreview && $missingBrandingKeys->isNotEmpty())
            <div class="container" style="margin-top: 14px;">
                <div style="
                    background: linear-gradient(90deg, rgba(255,193,7,0.14), rgba(255,193,7,0.08));
                    border: 1px solid rgba(255, 193, 7, 0.35);
                    color: #6b5000;
                    padding: 12px 14px;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 12px;
                ">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <strong style="white-space:nowrap;">تنبيه إداري</strong>
                        <span style="opacity:.95;">
                            بعض بيانات الموقع غير مكتملة، عدّلها من لوحة التحكم &gt; الهوية البصرية.
                        </span>
                    </div>
                    <a href="{{ route('admin.settings.branding') }}"
                       style="
                        white-space:nowrap;
                        padding: 8px 10px;
                        border-radius: 10px;
                        background: rgba(255, 193, 7, 0.22);
                        border: 1px solid rgba(255, 193, 7, 0.45);
                        color: inherit;
                        font-weight: 800;
                       ">
                        فتح الإعدادات
                    </a>
                </div>
                <div style="margin-top:8px;color:#6b7280;font-size:13px;">
                    الحقول الناقصة: {{ $missingBrandingKeys->implode('، ') }}
                </div>
            </div>
        @endif

        <!-- بداية رسائل الفلاش (نجاح/أخطاء) القادمة من عمليات POST مثل نماذج التواصل -->
        @if(session('success'))
            <div class="container" style="margin-top: 20px;">
                <div style="background:#1f7a1f;color:#fff;padding:12px 16px;border-radius:8px;">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="container" style="margin-top: 20px;">
                <div style="background:#8b1d1d;color:#fff;padding:12px 16px;border-radius:8px;">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif
        <!-- نهاية رسائل الفلاش -->

        <!-- بداية محتوى الصفحة المتغير -->
        @yield('content')
        <!-- نهاية محتوى الصفحة المتغير -->
    </main>

    <!-- ═══ FOOTER (يعتمد على $siteSettings و $siteMenu من لوحة التحكم) ═══ -->
    @include('site.partials.footer')

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @yield('scripts')
</body>
</html>
