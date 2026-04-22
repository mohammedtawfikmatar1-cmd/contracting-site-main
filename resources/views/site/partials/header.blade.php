<header id="navbar" class="site-header" role="banner">
    <div class="container header-inner">
        <!--
            خريطة تدفق البيانات (Header):
            - اسم الشركة/الشعار: يأتي من إعدادات لوحة التحكم (company_name, logo_main).
            - الشعار يُعرض داخل إطار دائري واسم الشركة أسفله على الشاشات الأوسع من 640px لتفادي ازدحام الشريط.
            - القائمة الرئيسية: من site_menu أو الافتراضي ($siteMenu).
            - الصفحات المنشورة من «إدارة المحتوى ← الصفحات» ($sitePages): تظهر في قائمة منسدلة بعنوان ثابت «صفحات إضافية».
            - على الشاشات الصغيرة: زر القائمة + لوحة منزلقة وربط "عرض سعر" داخل القائمة.
        -->
        <a href="{{ route('home') }}" class="logo" aria-label="موقع الشركة">
            <span class="logo-mark">
                <img src="{{ $settingsMedia('logo_main', asset('building.png'), asset('building.png')) }}" alt="{{ $settingsValue(['company_name', 'site_name'], 'شعار الشركة', 'شعار الموقع') }}" width="48" height="48" decoding="async">
            </span>
            <span class="logo-name">{{ $settingsValue(['company_name', 'site_name'], 'شركة مقاولات نموذجية', '') }}</span>
        </a>

        {{-- العمود الأوسط: التنقل فقط (البحث وزر العرض في عمود منفصل لتجنب التداخل مع الروابط) --}}
        <div class="header-cluster">
            <nav class="nav nav--primary" aria-label="التنقل الرئيسي">
                <button class="menu-toggle" id="mobile-menu" type="button"
                        aria-label="فتح القائمة" aria-controls="primary-nav" aria-expanded="false">
                    <span class="bar" aria-hidden="true"></span>
                    <span class="bar" aria-hidden="true"></span>
                    <span class="bar" aria-hidden="true"></span>
                </button>
                <ul id="primary-nav" class="nav-links" role="list">
                    @foreach(($siteMenu ?? collect())->take(12) as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="{{ !empty($item['active']) ? 'active' : '' }}">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach

                    @if(isset($sitePages) && $sitePages->isNotEmpty())
                        {{-- الصفحات المنشورة من لوحة التحكم: مجموعة تحت عنوان واضح دون كسر تخطيط الشريط --}}
                        <li class="nav-item nav-item--dropdown">
                            <button type="button" class="nav-dropdown-toggle" id="static-pages-nav-toggle"
                                    aria-expanded="false" aria-haspopup="true" aria-controls="static-pages-nav-menu">
                                صفحات إضافية
                                <i class="fas fa-chevron-down nav-dropdown-chevron" aria-hidden="true"></i>
                            </button>
                            <ul id="static-pages-nav-menu" class="nav-dropdown-menu" role="list">
                                @foreach($sitePages as $page)
                                    <li>
                                        <a href="{{ route('pages.show', $page->slug) }}"
                                           class="{{ request()->routeIs('pages.show') && request()->route('slug') === $page->slug ? 'active' : '' }}">
                                            {{ $page->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                    {{-- يظهر فقط ضمن قائمة الجوال (CSS) لأن زر عرض السعر يُخفى في الشريط العلوي على الشاشات الضيقة --}}
                    <li class="nav-mobile-cta">
                        <a href="{{ route('contact') }}#contact" class="nav-mobile-cta__link">عرض سعر</a>
                    </li>
                </ul>
                <div class="nav-overlay" data-nav-overlay aria-hidden="true"></div>
            </nav>
        </div>

        {{-- عمود الأدوات: البحث + عرض سعر — منفصل عن حزمة الروابط لتفادي التداخل على الشاشات > 640px --}}
        <div class="header-actions">
            <form action="{{ route('search') }}" method="GET" class="header-search-form" role="search">
                <label class="visually-hidden" for="header-search-q">بحث في الموقع</label>
                <input id="header-search-q" type="search" name="q" value="{{ request('q') }}" placeholder="ابحث..." autocomplete="off" class="header-search-form__input">
                <button type="submit" class="header-search-form__submit" aria-label="تنفيذ البحث">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
            </form>

            <a href="{{ route('contact') }}#contact" class="btn-quote">
                <span>عرض سعر</span>
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</header>
