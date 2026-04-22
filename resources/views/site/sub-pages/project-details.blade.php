@extends('site.layouts.app')

@section('title', $project->title . ' | مشاريع ' . ($siteSettings['company_name'] ?? 'شركة مقاولات'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $project->description), 160))
@section('og_type', 'article')
@section('og_image', $project->image_url ?: asset('imag/m1.jpg'))
@section('structured_data')
    @php
        /* بيانات منظمة للمشروع مع مسار التنقل */
        $projectStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $project->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $project->description), 200),
            'url' => route('projects.details', $project->slug),
            'image' => $project->image_url ?: asset('imag/m1.jpg'),
        ];
        $projectBreadcrumbData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'المشاريع', 'item' => route('projects')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $project->title, 'item' => route('projects.details', $project->slug)],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($projectStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($projectBreadcrumbData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('styles')
    @vite(['resources/css/project-details.css', 'resources/css/service-details.css'])
@endsection

@section('content')
<div class="project-details-page">
    <section class="project-hero" style="--hero-bg-image: url('{{ $project->image_url ?: asset('imag/m1.jpg') }}');">
        <div class="container">
            @include('site.partials.breadcrumbs', [
                'items' => [
                    ['label' => 'الرئيسية', 'url' => route('home')],
                    ['label' => 'المشاريع', 'url' => route('projects')],
                    ['label' => $project->title],
                ],
            ])
            <span class="sec-label">تفاصيل المشروع</span>
            <!-- بيانات المشروع: $project قادمة من SiteController@projectDetails (مُدار من لوحة التحكم: المشاريع) -->
            <h1>{{ $project->title }}</h1>
        </div>
    </section>

    <div class="container">
        <div class="project-meta-bar reveal-up">
            <div class="meta-item">
                <span>الموقع</span>
                <strong>{{ $project->location ?: '-' }}</strong>
            </div>
            <div class="meta-item">
                <span>التصنيف</span>
                <strong>{{ $project->category ?: '-' }}</strong>
            </div>
            <div class="meta-item">
                <span>الحالة</span>
                <strong>{{ $project->status_text ?: 'غير محدد' }}</strong>
            </div>
            <div class="meta-item">
                <span>التاريخ</span>
                <strong>{{ optional($project->completion_date)->format('Y-m-d') ?: '-' }}</strong>
            </div>
        </div>

        <div class="service-content-grid">
            <div class="service-main-info reveal-up">
                <h2>عن المشروع</h2>
                @php
                    $projectBody = (string) ($project->description ?? '');
                    $projectIsHtml = str_contains($projectBody, '<');
                @endphp
                @if($projectIsHtml)
                    <div class="service-description">{!! $projectBody !!}</div>
                @else
                    <p class="service-description">{!! nl2br(e($projectBody)) !!}</p>
                @endif

                @if($relatedProjects->isNotEmpty())
                    <h2>مشاريع مشابهة</h2>
                    <div class="achievements-list">
                        <!-- بداية حلقة المشاريع المشابهة: $relatedProjects قادمة من SiteController@projectDetails -->
                        @foreach($relatedProjects as $relatedProject)
                            <div class="achievement-item">
                                <i class="fas fa-check-circle"></i>
                                <a href="{{ route('projects.details', $relatedProject->slug) }}">{{ $relatedProject->title }}</a>
                            </div>
                        @endforeach
                        <!-- نهاية حلقة المشاريع المشابهة -->
                    </div>
                @endif
            </div>

            <aside class="request-sidebar reveal-up" style="--delay: 200ms">
                <div class="request-card">
                    <h3>اطلب خدمة مماثلة</h3>
                    <p style="font-size: var(--t-xs); color: var(--ink-s); margin-bottom: var(--s4); text-align: center;">هل أعجبك هذا المشروع؟ اطلب خدمتنا الآن</p>
                    <!--
                        نموذج تواصل مرتبط بمشروع:
                        يرسل إلى route('contact.store') => ContactRequestController@storeGeneral
                        مع request_type=service و service_requested="Project: <title>" لتمييز المصدر داخل لوحة التحكم.
                    -->
                    <form action="{{ route('contact.store') }}" method="POST" class="request-form">
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
                            <input type="email" id="email" name="email" class="form-control">
                            <input type="hidden" name="request_type" value="service">
                            <input type="hidden" name="service_requested" value="Project: {{ $project->title }}">
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
</div>
@endsection
