@extends('site.layouts.app')

@section('title', 'شركة مقاولات - من نحن')

@section('styles')
@vite(['resources/css/about.css'])
    {{-- <link rel="stylesheet" href="{{ asset('css/about.css') }}" /> --}}
@endsection

@section('content')
@php
    $isAdminPreview = auth()->check();
@endphp
<section id="about" class="about section-py">
    <div class="container">
        <div class="about-grid">
            <div class="about-visuals reveal">
                <div class="aimg-main">
                    <!-- صورة صفحة من نحن: مصدرها إعدادات لوحة التحكم (about_main_image) -->
                    @if(!empty($siteSettings['about_main_image']))
                        <img src="{{ $siteSettings['about_main_image'] }}" alt="عن الشركة" loading="lazy">
                    @else
                        <!-- بدل الصورة الافتراضية: نص يوضح إمكانية إضافتها من لوحة التحكم -->
                        <div style="
                            min-height: 280px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                            padding: 18px;
                            border: 1px dashed rgba(255,255,255,.35);
                            border-radius: 16px;
                            color: rgba(255,255,255,.88);
                            background: rgba(255,255,255,.04);
                            font-weight: 700;
                            line-height: 1.8;
                        ">
                            يمكن إضافة صورة صفحة "من نحن" من لوحة التحكم
                            <br>
                            (الإعدادات &gt; صفحة من نحن)
                        </div>
                    @endif
                </div>
            </div>
            <div class="about-text reveal" style="--delay:120ms">
                <span class="sec-label">من نحن</span>
                <!-- نصوص من نحن: مصدرها إعدادات لوحة التحكم (about_title/about_text_1/about_text_2) -->
                <h2>{{ $siteSettings['about_title'] ?? ($isAdminPreview ? 'نبذة تعريفية: أضف اسم أو رسالة "من نحن" من لوحة التحكم' : '') }}</h2>
                <p>{{ $siteSettings['about_text_1'] ?? ($isAdminPreview ? 'نص توضيحي: قدّم وصفًا موجزًا لهوية الشركة وخبرتها في مجال المقاولات.' : '') }}</p>
                <p>{{ $siteSettings['about_text_2'] ?? ($isAdminPreview ? 'نص توضيحي: أضف الرؤية أو الرسالة والقيم التي تميز شركتك.' : '') }}</p>
                <div class="about-stats" aria-label="إحصائيات">
                    <!-- إحصاءات الصفحة: $stats قادمة من SiteController@about وتعكس بيانات أقسام الإدارة (مشاريع/خدمات/وظائف) -->
                    <div class="ast"><h4>{{ $stats['projects'] }}</h4><p>مشروع</p></div>
                    <div class="ast"><h4>{{ $stats['services'] }}</h4><p>خدمة</p></div>
                    <div class="ast"><h4>{{ $stats['years'] }}</h4><p>سنة خبرة</p></div>
                    <div class="ast"><h4>{{ $stats['jobs'] }}</h4><p>وظيفة متاحة</p></div>
                </div>
                <a href="{{ route('contact') }}" class="cta-primary"><span>ابدأ مشروعك معنا</span></a>
            </div>
        </div>
    </div>
</section>
@endsection
