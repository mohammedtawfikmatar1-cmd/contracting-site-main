@extends('site.layouts.app')

@section('title', $tender->title . ' | تقديم عرض مناقصة | ' . ($siteSettings['company_name'] ?? 'شركة مقاولات'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $tender->description), 160))
@section('og_type', 'article')
@section('structured_data')
    @php
        /* بيانات منظمة للمناقصة مع مسار التنقل */
        $tenderStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $tender->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $tender->description), 200),
            'url' => route('tenders.request', $tender->id),
        ];
        $tenderBreadcrumbData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'المناقصات', 'item' => route('tenders')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $tender->title, 'item' => route('tenders.request', $tender->id)],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($tenderStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($tenderBreadcrumbData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('styles')
    @vite(['resources/css/service-details.css'])
    <style>
        .tender-info {
            background: var(--orange-subtle);
            padding: var(--s6);
            border-radius: var(--r-xl);
            border: 1px solid var(--orange);
            margin-bottom: var(--s7);
        }
        .tender-info h3 { color: var(--orange); margin-bottom: var(--s3); }
        .tender-meta { display: grid; grid-template-columns: 1fr 1fr; gap: var(--s4); color: var(--tm); }
        .tender-meta span strong { color: #fff; }
    </style>
@endsection

@section('content')
<div class="service-details-page">
    <section class="service-hero">
        <div class="container">
            @include('site.partials.breadcrumbs', [
                'items' => [
                    ['label' => 'الرئيسية', 'url' => route('home')],
                    ['label' => 'المناقصات', 'url' => route('tenders')],
                    ['label' => $tender->title],
                ],
            ])
            <span class="sec-label">المناقصات والعقود</span>
            <h1>تقديم عرض مناقصة</h1>
            <!-- بيانات المناقصة: $tender قادمة من SiteController@tenderRequest (مُدارة من لوحة التحكم: المناقصات) -->
            <p>{{ $tender->title }}</p>
        </div>
    </section>

    <section class="section-py">
        <div class="container">
            <div class="service-content-grid">
                <div class="service-main-info reveal-up">
                    <div class="tender-info">
                        <h3>تفاصيل المناقصة المختارة</h3>
                        <div class="tender-meta">
                            <span>المعرف: <strong>#{{ $tender->id }}</strong></span>
                            <span>تاريخ الإغلاق: <strong>{{ optional($tender->closing_date)->format('Y-m-d H:i') }}</strong></span>
                            <span>نوع العمل: <strong>{{ $tender->work_type ?: '-' }}</strong></span>
                            <span>الموقع: <strong>{{ $tender->location ?: '-' }}</strong></span>
                        </div>
                    </div>

                    <h2>تعليمات تقديم العروض</h2>
                    @php
                        $tenderBody = (string) ($tender->description ?? '');
                        $tenderIsHtml = str_contains($tenderBody, '<');
                    @endphp
                    @if($tenderIsHtml)
                        <div class="service-description">{!! $tenderBody !!}</div>
                    @else
                        <p class="service-description">{!! nl2br(e($tenderBody)) !!}</p>
                    @endif

                    <div class="achievements-list">
                        <div class="achievement-item">
                            <i class="fas fa-info-circle"></i>
                            <span>يتم تقييم العروض بناءً على الجودة والسعر وسابقة الأعمال.</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-lock"></i>
                            <span>جميع البيانات المقدمة تعامل بسرية تامة.</span>
                        </div>
                    </div>
                </div>

                <aside class="request-sidebar reveal-up" style="--delay: 200ms">
                    <div class="request-card">
                        <h3>نموذج تقديم العرض</h3>
                        <!--
                            نموذج تقديم عرض مناقصة:
                            يرسل إلى route('tenders.request.store') => ContactRequestController@storeTenderRequest
                            ثم يُخزن الطلب في جدول contacts ويُرفع proposal_file (إن وجد) ويظهر للإدارة ضمن الرسائل/الطلبات.
                        -->
                        <form action="{{ route('tenders.request.store', $tender) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="company">اسم الشركة / المسؤول</label>
                                <input type="text" id="company" name="full_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">رقم التواصل</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="proposal">العرض الفني والمالي (PDF)</label>
                                <input type="file" id="proposal" name="proposal_file" class="form-control" accept=".pdf">
                            </div>
                            <div class="form-group">
                                <label for="notes">تفاصيل العرض</label>
                                <textarea id="notes" name="message" class="form-control" placeholder="أي تفاصيل أخرى تود إضافتها..." required></textarea>
                            </div>
                            <button type="submit" class="cta-primary" style="width: 100%; justify-content: center;">
                                <span>إرسال العرض</span>
                                <span class="cta-ico"><i class="fas fa-file-contract"></i></span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
