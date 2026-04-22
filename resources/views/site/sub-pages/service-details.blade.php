@extends('site.layouts.app')

@section('title', $service->title . ' | خدمات ' . ($siteSettings['company_name'] ?? 'شركة مقاولات'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) ($service->overview ?: $service->description)), 160))
@section('og_type', 'article')
@section('og_image', $service->image_url ?: asset('imag/m1.jpg'))
@section('structured_data')
    @php
        /* بيانات منظمة للخدمة + مسار تنقل لمساعدة محركات البحث على فهم الصفحة */
        $serviceStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) ($service->overview ?: $service->description)), 200),
            'url' => route('services.details', $service->slug),
            'image' => $service->image_url ?: asset('imag/m1.jpg'),
            'provider' => [
                '@type' => 'Organization',
                'name' => $siteSettings['company_name'] ?? 'شركة مقاولات',
            ],
        ];
        $serviceBreadcrumbData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'الخدمات', 'item' => route('services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $service->title, 'item' => route('services.details', $service->slug)],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($serviceStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($serviceBreadcrumbData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('styles')
    @vite(['resources/css/service-details.css'])
@endsection

@section('content')
<div class="service-details-page">
    <section class="service-hero" style="--hero-bg-image: url('{{ $service->image_url ?: asset('imag/m1.jpg') }}');">
        <div class="container">
            @include('site.partials.breadcrumbs', [
                'items' => [
                    ['label' => 'الرئيسية', 'url' => route('home')],
                    ['label' => 'الخدمات', 'url' => route('services')],
                    ['label' => $service->title],
                ],
            ])
            <span class="sec-label">تفاصيل الخدمة</span>
            <!-- بيانات الخدمة: $service قادمة من SiteController@serviceDetails (مُدارة من لوحة التحكم: الخدمات) -->
            <h1>{{ $service->title }}</h1>
            <p>{{ \Illuminate\Support\Str::limit(strip_tags($service->overview ?: $service->description), 160) }}</p>
        </div>
    </section>

    <section class="section-py">
        <div class="container">
            <div class="service-content-grid">
                <div class="service-main-info reveal-up">
                    <h2>نظرة عامة على الخدمة</h2>
                    @php
                        $serviceBody = (string) ($service->description ?? '');
                        $serviceIsHtml = str_contains($serviceBody, '<');
                    @endphp
                    @if($serviceIsHtml)
                        <div class="service-description">{!! $serviceBody !!}</div>
                    @else
                        <p class="service-description">{!! nl2br(e($serviceBody)) !!}</p>
                    @endif

                    @if($relatedProjects->isNotEmpty())
                        <h2>مشاريع مرتبطة بهذه الخدمة</h2>
                        <div class="achievements-list">
                            <!-- بداية حلقة المشاريع المرتبطة: $relatedProjects قادمة من SiteController@serviceDetails (مُدارة من لوحة التحكم: المشاريع) -->
                            @foreach($relatedProjects as $project)
                                <div class="achievement-item">
                                    <i class="fas fa-check-circle"></i>
                                    <a href="{{ route('projects.details', $project->slug) }}">{{ $project->title }}</a>
                                </div>
                            @endforeach
                            <!-- نهاية حلقة المشاريع المرتبطة -->
                        </div>
                    @endif
                </div>

                <aside class="request-sidebar reveal-up" style="--delay: 200ms">
                    <div class="request-card">
                        <h3>طلب هذه الخدمة</h3>
                        <!--
                            نموذج طلب خدمة:
                            يرسل إلى route('services.request') => ContactRequestController@storeServiceRequest
                            ثم يُخزن الطلب في جدول contacts ويظهر في لوحة التحكم ضمن "الرسائل والطلبات".
                        -->
                        <form action="{{ route('services.request', $service) }}" method="POST" class="request-form">
                            @csrf
                            <div class="form-group">
                                <label for="name">الاسم الكامل</label>
                                <input type="text" id="name" name="full_name" class="form-control" placeholder="أدخل اسمك الكامل" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">رقم الهاتف</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="00967..." required>
                            </div>
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="example@mail.com">
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="service_requested" value="{{ $service->title }}">
                            </div>
                            <div class="form-group">
                                <label for="message">تفاصيل إضافية</label>
                                <textarea id="message" name="message" class="form-control" placeholder="اشرح لنا المزيد عن طلبك..."></textarea>
                            </div>
                            <button type="submit" class="cta-primary" style="width: 100%; justify-content: center;">
                                <span>إرسال الطلب</span>
                                <span class="cta-ico"><i class="fas fa-paper-plane"></i></span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
