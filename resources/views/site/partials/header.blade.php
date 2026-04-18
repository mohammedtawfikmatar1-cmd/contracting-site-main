<header id="navbar" class="site-header" role="banner">
    <div class="container header-inner">
        <!--
            خريطة تدفق البيانات (Header):
            - اسم الشركة/الشعار: يأتي من إعدادات لوحة التحكم (company_name, logo_main).
            - القائمة الرئيسية: تأتي من إعدادات لوحة التحكم (site_menu) وتُمرر كـ $siteMenu.
            - زر تبديل اللغة: يظهر فقط إذا فعّلت الإدارة enable_multilingual.
        -->
        <a href="{{ route('home') }}" class="logo" aria-label="موقع الشركة">
            <span class="logo-name">{{ $settingsValue(['company_name', 'site_name'], 'شركة مقاولات نموذجية', '') }}</span>
            <img src="{{ $settingsMedia('logo_main', asset('building.png'), asset('building.png')) }}" alt="{{ $settingsValue('company_name', 'شعار الشركة', 'شعار الموقع') }}">
        </a>

        <nav class="nav" aria-label="التنقل الرئيسي">
            <button class="menu-toggle" id="mobile-menu" type="button"
                    aria-label="فتح القائمة" aria-controls="primary-nav" aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <ul id="primary-nav" class="nav-links" role="list">
                <!-- بداية روابط القائمة (قادمة من إعدادات الإدارة: site_menu) -->
                @foreach(($siteMenu ?? collect())->take(12) as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="{{ !empty($item['active']) ? 'active' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
                <!-- نهاية روابط القائمة -->
            </ul>
            <div class="nav-overlay" data-nav-overlay></div>
        </nav>

        <!-- بداية نموذج البحث (يمرر q إلى SiteController@search) -->
        <form action="{{ route('search') }}" method="GET" style="display:flex;align-items:center;gap:8px;">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث في الموقع..." style="padding:8px 10px;border-radius:8px;border:1px solid #ddd;">
            <button type="submit" style="padding:8px 10px;border:none;border-radius:8px;background:var(--theme-primary, #f58220);color:#fff;">
                <i class="fas fa-search"></i>
            </button>
        </form>
        <!-- نهاية نموذج البحث -->

        @if(!empty($siteSettings['enable_multilingual']))
            @php($nextLocale = app()->getLocale() === 'en' ? 'ar' : 'en')
            <a href="{{ route('lang.switch', $nextLocale) }}"
               style="padding:8px 10px;border-radius:10px;border:1px solid rgba(255,255,255,.25);color:#fff;background:rgba(255,255,255,.08);font-weight:800;">
                {{ strtoupper($nextLocale) }}
            </a>
        @endif

        <a href="{{ route('contact') }}#contact" class="btn-quote">
            <span>عرض سعر</span>
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
        </a>
    </div>
</header>
