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
        $siteName = trim((string) ($siteSettings['company_name'] ?? $siteSettings['site_name'] ?? 'شركة مقاولات'));
        $currentUrl = request()->fullUrl();
        $currentQuery = request()->getQueryString();
        $canonicalUrl = trim($__env->yieldContent('canonical', $currentQuery ? $currentUrl : url()->current()));
        $metaRobots = trim($__env->yieldContent('robots', 'index,follow'));
        $pageTitle = trim($__env->yieldContent('title', $siteName . ' - الموقع الرسمي'));
        $pageDescription = trim($__env->yieldContent('description', 'شركة مقاولات متخصصة في تنفيذ مشاريع البناء والتشييد بجودة عالية.'));
        $pageType = trim($__env->yieldContent('og_type', 'website'));
        $pageImage = trim($__env->yieldContent('og_image', (string) ($siteSettings['logo_main'] ?? $siteSettings['logo_transparent'] ?? asset('building.png'))));
        $twitterCard = trim($__env->yieldContent('twitter_card', 'summary_large_image'));
        $structuredData = trim($__env->yieldContent('structured_data'));
        $searchTarget = route('search') . '?q={search_term_string}';
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => route('home'),
            'logo' => $pageImage,
            'email' => $siteSettings['company_email'] ?? null,
            'telephone' => $siteSettings['company_phone'] ?? null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $siteSettings['company_address'] ?? null,
            ],
            'sameAs' => collect([
                $siteSettings['social_facebook'] ?? null,
                $siteSettings['social_x'] ?? null,
                $siteSettings['social_instagram'] ?? null,
                $siteSettings['social_linkedin'] ?? null,
                $siteSettings['social_youtube'] ?? null,
            ])->filter()->values()->all(),
        ];
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => route('home'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $searchTarget,
                'query-input' => 'required name=search_term_string',
            ],
        ];

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

        $brandingKeyLabels = [
            'company_name' => 'اسم الشركة',
            'company_phone' => 'رقم التواصل',
            'company_email' => 'البريد الإلكتروني',
            'company_address' => 'العنوان',
            'logo_main' => 'الشعار الأساسي',
        ];

        $missingBrandingKeys = collect($brandingRequiredKeys)
            ->filter(fn ($key) => empty($siteSettings[$key] ?? null))
            ->values();
    @endphp

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ $pageDescription }}" />
    <meta name="robots" content="{{ $metaRobots }}" />
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ $settingsMedia('favicon', asset('favicon.ico'), asset('favicon.ico')) }}">
    <meta property="og:locale" content="ar_AR">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:type" content="{{ $pageType }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">
    
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
        $normalizeColor = function ($value, string $fallback): string {
            return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', trim($value))
                ? trim($value)
                : $fallback;
        };

        $themePrimary = $siteSettings['theme_primary_color'] ?? null;
        $themeSecondary = $siteSettings['theme_secondary_color'] ?? null;
        $themeAccent = $siteSettings['theme_accent_color'] ?? null;
        $themePrimary = $normalizeColor($themePrimary, '#ff7a1a');
        $themeSecondary = $normalizeColor($themeSecondary, '#0b5ed7');
        $themeAccent = $normalizeColor($themeAccent, '#22c55e');
        $bodyBgColor = $normalizeColor($siteSettings['body_bg_color'] ?? null, '#0d0f14');
        $footerBgColor = $normalizeColor($siteSettings['footer_bg_color'] ?? null, '#07080b');
        $headerBgColor = $normalizeColor($siteSettings['header_bg_color'] ?? null, '#0d0f14');
        $headerScrolledBgColor = $normalizeColor($siteSettings['header_scrolled_bg_color'] ?? null, '#0d0f14');
        $headerTextColor = $normalizeColor($siteSettings['header_text_color'] ?? null, '#f8fafc');
        $footerTextColor = $normalizeColor($siteSettings['footer_text_color'] ?? null, '#f8fafc');
        $contentTextColor = $normalizeColor($siteSettings['content_text_color'] ?? null, '#f8fafc');
    @endphp
    <style>
        :root {
            --theme-primary: {{ $themePrimary }};
            --theme-secondary: {{ $themeSecondary }};
            --theme-accent: {{ $themeAccent }};
            --site-body-bg: {{ $bodyBgColor }};
            --site-footer-bg: {{ $footerBgColor }};
            --site-header-bg: {{ $headerBgColor }};
            --site-header-scrolled-bg: {{ $headerScrolledBgColor }};
            --site-header-text: {{ $headerTextColor }};
            --site-footer-text: {{ $footerTextColor }};
            --site-content-text: {{ $contentTextColor }};

            /* ربط رموز التصميم الحالية باللون الرئيسي من لوحة التحكم */
            --orange: var(--theme-primary);
            --orange-light: color-mix(in srgb, var(--theme-primary) 78%, white);
            --orange-dark: color-mix(in srgb, var(--theme-primary) 78%, black);
            --orange-glow: color-mix(in srgb, var(--theme-primary) 22%, transparent);
            --orange-subtle: color-mix(in srgb, var(--theme-primary) 10%, transparent);
            --tw: var(--site-header-text);
            --tm: color-mix(in srgb, var(--site-header-text) 78%, transparent);
            --ts: color-mix(in srgb, var(--site-header-text) 54%, transparent);
        }

        body {
            background: var(--site-body-bg);
        }

        main.site-main {
            color: var(--site-content-text);
            --ink: var(--site-content-text);
            --ink-m: color-mix(in srgb, var(--site-content-text) 78%, var(--site-body-bg));
            --ink-s: color-mix(in srgb, var(--site-content-text) 58%, var(--site-body-bg));
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

        @media (max-width: 640px) {
            .admin-preview-toolbar .toolbar-inner {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .admin-preview-toolbar .toolbar-actions {
                justify-content: center;
            }
        }
    </style>
    <!-- بيانات منظمة أساسية لتعريف الشركة والموقع لمحركات البحث -->
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @if($structuredData !== '')
        <!-- بيانات منظمة إضافية خاصة بالصفحة الحالية -->
        {!! $structuredData !!}
    @endif
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

    <!-- المحتوى الرئيسي: تُطبَّق عليه معايير تباين النص في global.css دون المساس بهيدر/فوتر -->
    <main class="site-main">
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
                        <strong style="white-space:nowrap;">تنبيه للمسؤول</strong>
                        <span style="opacity:.95;">
                            بعض بيانات الموقع الأساسية غير مكتملة. أكملها من لوحة التحكم &gt; الهوية البصرية.
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
                    الحقول الناقصة:
                    {{ $missingBrandingKeys->map(fn ($k) => $brandingKeyLabels[$k] ?? $k)->implode('، ') }}
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
    @if($isAdminPreview)
        <script>
            (function () {
                // ضبط إزاحة الهيدر تلقائيا بحسب ارتفاع شريط المعاينة لتجنب التداخل على الجوال.
                const toolbar = document.querySelector('.admin-preview-toolbar');
                if (!toolbar) {
                    return;
                }

                const applyOffset = function () {
                    const height = Math.ceil(toolbar.getBoundingClientRect().height || 48);
                    document.body.style.setProperty('--admin-toolbar-offset', height + 'px');
                };

                applyOffset();
                window.addEventListener('resize', applyOffset);
            })();
        </script>
    @endif
    @yield('scripts')
</body>
</html>
