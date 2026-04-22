@extends('site.layouts.app')

@section('title', $newsItem->title . ' | أخبار ' . ($siteSettings['company_name'] ?? 'شركة مقاولات'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $newsItem->content), 160))
@section('og_type', 'article')
@section('og_image', $newsItem->image_url ?: asset('imag/m1.jpg'))
@section('structured_data')
    @php
        /* بيانات منظمة للخبر مع تاريخ النشر وصورة الخبر */
        $newsStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $newsItem->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $newsItem->content), 200),
            'datePublished' => optional($newsItem->published_at)->toIso8601String(),
            'dateModified' => optional($newsItem->updated_at)->toIso8601String(),
            'image' => [$newsItem->image_url ?: asset('imag/m1.jpg')],
            'mainEntityOfPage' => route('news.details', $newsItem->slug),
            'author' => [
                '@type' => 'Organization',
                'name' => $siteSettings['company_name'] ?? 'شركة مقاولات',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteSettings['company_name'] ?? 'شركة مقاولات',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $siteSettings['logo_main'] ?? asset('building.png'),
                ],
            ],
        ];
        $newsBreadcrumbData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'الأخبار', 'item' => route('news')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $newsItem->title, 'item' => route('news.details', $newsItem->slug)],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($newsStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($newsBreadcrumbData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('styles')
    @vite(['resources/css/news.css', 'resources/css/service-details.css'])
    <style>
        .news-article-content {
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.85;
            font-size: var(--t-md);
        }
        .news-body-html a {
            color: var(--orange);
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .news-article-content h2, .news-article-content h3 {
            color: rgba(255, 255, 255, 0.96);
            margin: var(--s6) 0 var(--s4);
        }
        .news-article-content p {
            margin-bottom: var(--s5);
        }
        .news-article-content img {
            border-radius: var(--r-xl);
            margin: var(--s6) 0;
            width: 100%;
            height: auto;
            border: 1px solid rgba(255, 122, 26, 0.1);
        }
        .share-article {
            margin-top: var(--s8);
            padding-top: var(--s6);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: var(--s4);
        }
        .share-article span {
            color: var(--tw);
            font-weight: 700;
        }
        .share-links {
            display: flex;
            gap: var(--s3);
        }
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            display: grid;
            place-items: center;
            color: var(--ts);
            transition: all var(--tf);
        }
        .share-btn:hover {
            background: var(--orange);
            color: var(--tw);
            transform: translateY(-3px);
        }
        .related-news {
            margin-top: var(--s10);
        }
        .related-news h2 {
            color: var(--tw);
            margin-bottom: var(--s6);
        }
    </style>
@endsection

@section('content')
<div class="service-details-page">
    <section class="service-hero" style="--hero-bg-image: url('{{ $newsItem->image_url ?: asset('imag/m1.jpg') }}');">
        <div class="container">
            @include('site.partials.breadcrumbs', [
                'items' => [
                    ['label' => 'الرئيسية', 'url' => route('home')],
                    ['label' => 'الأخبار', 'url' => route('news')],
                    ['label' => $newsItem->title],
                ],
            ])
            <div class="hero-content reveal">
                <span class="sec-label">أخبار وتحديثات</span>
                <!-- بيانات الخبر: $newsItem قادمة من SiteController@newsDetails (مُدار من لوحة التحكم: الأخبار) -->
                <h1>{{ $newsItem->title }}</h1>
                <div class="news-meta" style="justify-content: center; margin-top: var(--s4);">
                    <span class="news-category">{{ $newsItem->category ?: 'أخبار' }}</span>
                    <span class="news-date"><i class="far fa-calendar-alt"></i> {{ optional($newsItem->published_at)->format('Y-m-d') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section-py">
        <div class="container">
            <div class="service-content-grid">
                <div class="service-main-info reveal-up">
                    <div class="news-article-content">
                        <img src="{{ $newsItem->image_url ?: asset('imag/m1.jpg') }}" alt="{{ $newsItem->title }}">
                        @php
                            $body = (string) $newsItem->content;
                            $isHtml = str_contains($body, '<');
                        @endphp
                        @if($isHtml)
                            <div class="news-body-html">{!! $body !!}</div>
                        @else
                            <div class="news-body-plain">{!! nl2br(e($body)) !!}</div>
                        @endif
                    </div>

                    <div class="related-news">
                        <h2>أخبار ذات صلة</h2>
                        <div class="news-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                            <!-- بداية حلقة الأخبار ذات الصلة: $relatedNews قادمة من SiteController@newsDetails -->
                            @forelse($relatedNews as $related)
                                <article class="news-card">
                                    <a class="news-card__media" href="{{ route('news.details', $related->slug) }}" tabindex="-1" aria-hidden="true">
                                        <div class="news-image news-image-sm">
                                            <img src="{{ $related->image_url ?: asset('imag/m1.jpg') }}" alt="{{ $related->title }}" loading="lazy">
                                        </div>
                                    </a>
                                    <div class="news-content">
                                        <h3><a href="{{ route('news.details', $related->slug) }}">{{ $related->title }}</a></h3>
                                        <p class="news-excerpt news-excerpt--compact">{{ $related->getExcerpt(100) }}</p>
                                        <a href="{{ route('news.details', $related->slug) }}" class="news-link"><span>اقرأ المزيد</span></a>
                                    </div>
                                </article>
                            @empty
                                <p>لا توجد أخبار ذات صلة.</p>
                            @endforelse
                            <!-- نهاية حلقة الأخبار ذات الصلة -->
                        </div>
                    </div>
                </div>

                <aside class="request-sidebar reveal-up" style="--delay: 200ms">
                    <div class="request-card" style="margin-top: var(--s6); background: linear-gradient(135deg, var(--orange-light), var(--orange-dark));">
                        <h3 style="color: var(--tw);">هل لديك استفسار؟</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: var(--t-sm); margin-bottom: var(--s5);">يمكنك التواصل معنا مباشرة لأي استفسار.</p>
                        <a href="{{ route('contact') }}" class="cta-primary" style="width: 100%; justify-content: center; background: var(--tw); color: var(--orange-dark);">
                            <span>تواصل معنا</span>
                            <span class="cta-ico"><i class="fas fa-headset"></i></span>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
