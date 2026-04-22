@extends('site.layouts.app')

@section('title', 'شركة مقاولات - الصفحة الرئيسية')

@section('styles')
@vite(['resources/css/index.css', 'resources/css/services.css', 'resources/css/news.css'])
    {{-- <link rel="stylesheet" href="{{ asset('css/index.css') }}" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/services.css') }}" /> --}}
@endsection

@section('content')
@php
    $isAdminPreview = auth()->check();
@endphp
<section id="home" class="hero" aria-label="الرئيسية">
    <div class="hero-media" aria-hidden="true">
        <!-- صورة الغلاف: مصدرها إعدادات لوحة التحكم (home_hero_image) -->
        <img src="{{ $siteSettings['home_hero_image'] ?? asset('imag/m1.jpg') }}" alt="">
    </div>
    <div class="container hero-body">
        <!--
            نصوص الهيرو: تُدار من لوحة التحكم > الهوية البصرية
            (home_hero_badge، home_hero_title، home_hero_description) وتُحفظ في جدول settings.
        -->
        <div class="hero-badge reveal-up">{{ $siteSettings['home_hero_badge'] ?? ($isAdminPreview ? 'بيان توضيحي: أضف شارة تعريفية للشركة من لوحة التحكم' : '') }}</div>
        <h1 class="hero-title reveal-up">{{ $siteSettings['home_hero_title'] ?? ($isAdminPreview ? 'عنوان رئيسي توضيحي: أضف رسالة الشركة هنا' : '') }}</h1>
        <p class="hero-desc reveal-up">{{ $siteSettings['home_hero_description'] ?? ($isAdminPreview ? 'وصف توضيحي: عرّف بخدمات شركتك ومجالات عملها من لوحة التحكم.' : '') }}</p>
        <div class="hero-actions reveal-up">
            <a href="{{ route('projects') }}" class="cta-primary"><span>استعرض مشاريعنا</span></a>
            <a href="{{ route('contact') }}" class="cta-ghost">تواصل معنا</a>
        </div>
    </div>
</section>

<section id="services" class="services section-py">
    <div class="container">
        <div class="sec-head reveal">
            <span class="sec-label">خدماتنا</span>
            <h2>حلول بناء متكاملة</h2>
        </div>
        <div class="svc-grid">
            <!-- بداية حلقة الخدمات: $services قادمة من SiteController@home (مُدارة من لوحة التحكم: الخدمات) -->
            @forelse($services as $service)
                <a class="svc-card reveal" href="{{ route('services.details', $service->slug) }}">
                    <div class="svc-icon"><i class="{{ $service->icon ?: 'fas fa-tools' }}"></i></div>
                    <h3>{{ $service->title }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($service->overview ?: $service->description), 110) }}</p>
                    <div class="svc-line"></div>
                </a>
            @empty
                <p>لا توجد خدمات منشورة حالياً.</p>
            @endforelse
            <!-- نهاية حلقة الخدمات -->
        </div>
    </div>
</section>

<section id="projects" class="projects section-py">
    <div class="container">
        <div class="sec-head reveal">
            <span class="sec-label">أعمالنا</span>
            <h2>أحدث المشاريع</h2>
        </div>
        <div class="proj-grid">
            <!-- بداية حلقة المشاريع: $projects قادمة من SiteController@home (مُدارة من لوحة التحكم: المشاريع) -->
            @forelse($projects as $project)
                <a class="proj-card reveal" href="{{ route('projects.details', $project->slug) }}">
                    <div class="proj-img">
                        <img src="{{ $project->image_url ?: asset('imag/m1.jpg') }}" alt="{{ $project->title }}">
                    </div>
                    <div class="proj-info">
                        <span class="proj-tag">{{ $project->category ?: 'مشروع' }}</span>
                        <h4>{{ $project->title }}</h4>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($project->description), 90) }}</p>
                    </div>
                </a>
            @empty
                <p>لا توجد مشاريع منشورة حالياً.</p>
            @endforelse
            <!-- نهاية حلقة المشاريع -->
        </div>

        @if(isset($homeClients) && $homeClients->isNotEmpty())
            <!--
                قسم عملاء الصفحة الرئيسية:
                - $homeClients من SiteController@home (عملاء منشورون ولهم مشاريع منشورة على الأقل).
                - الرابط يوجّه إلى صفحة /clients عند تفعيلها من الإدارة، وإلا إلى قسم العرض نفسه.
            -->
            @php
                $clientsPageOn = !empty($siteSettings['clients_page_enabled']);
            @endphp
            <div id="clients-showcase" class="clients-showcase mt-5 reveal" aria-label="عملاؤنا">
                <div class="sec-head" style="margin-bottom: 14px;">
                    <span class="sec-label">عملاؤنا</span>
                    <h3 class="h4 mb-0">شركاؤنا في التنفيذ</h3>
                </div>
                <div class="clients-marquee-wrap">
                    <div class="clients-marquee-track">
                        <div class="clients-marquee-group">
                            @foreach($homeClients as $client)
                                @php
                                    $href = $clientsPageOn
                                        ? route('clients').'#c-'.$client->slug
                                        : '#clients-showcase';
                                @endphp
                                <a class="clients-marquee-item" href="{{ $href }}">
                                    @if($client->logo_url)
                                        <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" loading="lazy">
                                    @endif
                                    <span>{{ $client->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

<section class="news-section section-py">
    <div class="container">
        <div class="sec-head reveal">
            <span class="sec-label">الأخبار</span>
            <h2>آخر المستجدات</h2>
        </div>
        <div class="news-grid">
            <!-- بداية حلقة الأخبار: $news قادمة من SiteController@home (مُدارة من لوحة التحكم: الأخبار) -->
            @forelse($news as $item)
                <article class="news-card reveal">
                    <a class="news-card__media" href="{{ route('news.details', $item->slug) }}" aria-hidden="true" tabindex="-1">
                        <div class="news-image">
                            <img src="{{ $item->image_url ?: asset('imag/m1.jpg') }}" alt="" loading="lazy">
                            <span class="news-date">{{ optional($item->published_at)->format('Y-m-d') }}</span>
                        </div>
                    </a>
                    <div class="news-content">
                        <div class="news-meta">
                            <span class="news-category">{{ $item->category ?: 'أخبار' }}</span>
                        </div>
                        <h3><a href="{{ route('news.details', $item->slug) }}">{{ $item->title }}</a></h3>
                        <p class="news-excerpt">{{ $item->getExcerpt(140) }}</p>
                        <a href="{{ route('news.details', $item->slug) }}" class="news-link"><span>اقرأ المزيد</span></a>
                    </div>
                </article>
            @empty
                <p>لا توجد أخبار منشورة حالياً.</p>
            @endforelse
            <!-- نهاية حلقة الأخبار -->
        </div>
    </div>
</section>
@endsection
