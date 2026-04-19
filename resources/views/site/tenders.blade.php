@extends('site.layouts.app')

@section('title', 'شركة مقاولات - المناقصات')

@section('styles')
@vite(['resources/css/tenders.css'])
@endsection

@section('content')
<section class="tenders-hero">
    <div class="container">
        <div class="hero-content reveal">
            <span class="sec-label">المناقصات</span>
            <h1>فرص تعاقدية متاحة</h1>
        </div>
    </div>
</section>

<section id="current-tenders" class="current-tenders section-py">
    <div class="container">
        <div class="tenders-list">
            <!-- بداية حلقة المناقصات: $tenders قادمة من SiteController@tenders (مُدارة من لوحة التحكم: المناقصات) -->
            @forelse($tenders as $tender)
                <div id="tender-{{ $tender->id }}" class="tender-card reveal">
                    <div class="tender-header">
                        <div class="tender-info">
                            <h3>{{ $tender->title }}</h3>
                        </div>
                        <span class="tender-status {{ $tender->status === 'open' ? 'active' : '' }}">{{ $tender->status }}</span>
                    </div>
                    <div class="tender-details">
                        <div class="detail-row"><span class="detail-label">نوع العمل:</span><span>{{ $tender->work_type ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">الموقع:</span><span>{{ $tender->location ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">آخر موعد:</span><span>{{ optional($tender->closing_date)->format('Y-m-d H:i') }}</span></div>
                    </div>
                    <p class="tender-desc">{{ \Illuminate\Support\Str::limit(strip_tags($tender->description), 140) }}</p>
                    <div class="tender-actions">
                        <a href="{{ route('tenders.request', $tender->id) }}" class="tender-apply"><span>تقديم عرض</span></a>
                    </div>
                </div>
            @empty
                <p>لا توجد مناقصات منشورة حالياً.</p>
            @endforelse
            <!-- نهاية حلقة المناقصات -->
        </div>
        <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
        <div style="margin-top: 20px;">{{ $tenders->links() }}</div>
    </div>
</section>
@endsection
