@extends('site.layouts.app')

@section('title', $page->title)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $page->content), 160))

@section('content')
<section class="section-py">
    <div class="container">
        @include('site.partials.breadcrumbs', [
            'items' => [
                ['label' => 'الرئيسية', 'url' => route('home')],
                ['label' => $page->title],
            ],
        ])
        <div class="sec-head">
            <span class="sec-label">صفحة</span>
            <!--
                خريطة تدفق البيانات (صفحة ديناميكية):
                - $page قادمة من SiteController@page (أو dynamicPage) من جدول pages.
                - الصفحة تُدار من لوحة التحكم عبر قسم "الصفحات" (Admin\PageController).
                - المحتوى هنا قد يكون HTML محفوظا، لذا يتم عرضه كما هو.
            -->
            <h1>{{ $page->title }}</h1>
        </div>
        <div class="service-main-info">
            <!-- محتوى الصفحة: مصدره لوحة التحكم (حقل content) -->
            {!! $page->content !!}
        </div>
    </div>
</section>
@endsection
