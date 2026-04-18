@extends('admin.layouts.app')

@section('title', 'بحث لوحة التحكم')
@section('page_title', 'نتائج البحث')

@section('breadcrumb')
    <li class="breadcrumb-item active">البحث</li>
@endsection

@section('content')
<div class="mb-3">
    <strong>كلمة البحث:</strong> {{ $q ?: '---' }}
</div>

@php
    // تجميع نتائج البحث حسب الكيان. مصدر البيانات: Admin\SearchController (بحث داخل لوحة التحكم).
    // هذه النتائج تساعد الإدارة على الوصول السريع للمحتوى الذي ينعكس في الواجهة (أو للرسائل الواردة).
    $sections = [
        'المشاريع' => $projects,
        'الخدمات' => $services,
        'الأخبار' => $news,
        'الصفحات' => $pages,
        'المناقصات' => $tenders,
        'الوظائف' => $jobs,
        'الرسائل' => $contacts,
    ];
@endphp

<!-- بداية أقسام النتائج -->
@foreach($sections as $name => $items)
    <div class="card mb-3">
        <div class="card-header"><strong>{{ $name }}</strong> ({{ $items->count() }})</div>
        <div class="card-body">
            @if($items->isEmpty())
                <span class="text-muted">لا نتائج.</span>
            @else
                <ul class="mb-0">
                    <!-- بداية عناصر القسم -->
                    @foreach($items as $item)
                        <li>
                            {{ $item->title ?? $item->full_name ?? ('#' . $item->id) }}
                        </li>
                    @endforeach
                    <!-- نهاية عناصر القسم -->
                </ul>
            @endif
        </div>
    </div>
@endforeach
<!-- نهاية أقسام النتائج -->
@endsection
