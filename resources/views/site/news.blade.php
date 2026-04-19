@extends('site.layouts.app')

@section('title', 'شركة مقاولات - الأخبار')

@section('styles')
@vite(['resources/css/news.css'])
    {{-- <link rel="stylesheet" href="{{ asset('css/news.css') }}" /> --}}
@endsection

@section('content')
<section class="news-hero">
    <div class="container">
        <div class="hero-content reveal">
            <span class="sec-label">آخر الأخبار</span>
            <h1>أخبار وتحديثات الشركة</h1>
        </div>
    </div>
</section>

<section class="news-section section-py">
    <div class="container">
        <div class="news-grid">
            <!-- بداية حلقة الأخبار: $news قادمة من SiteController@news (مُدارة من لوحة التحكم: الأخبار) -->
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
        <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
        <div style="margin-top: 20px;">{{ $news->links() }}</div>
    </div>
</section>
@endsection
