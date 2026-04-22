@extends('site.layouts.app')

@section('title', ($siteSettings['company_name'] ?? 'شركة مقاولات') . ' | الوظائف')
@section('description', 'اطلع على الوظائف المتاحة لدى الشركة وقدّم طلبك للانضمام إلى فريق العمل.')

@section('styles')
@vite(['resources/css/careers.css'])
@endsection

@section('content')
<section class="careers-hero">
    <div class="container">
        @include('site.partials.breadcrumbs', [
            'items' => [
                ['label' => 'الرئيسية', 'url' => route('home')],
                ['label' => 'الوظائف'],
            ],
        ])
        <div class="hero-content reveal">
            <span class="sec-label">انضم إلينا</span>
            <h1>الوظائف المتاحة</h1>
        </div>
    </div>
</section>

<section id="openings" class="openings section-py">
    <div class="container">
        <div class="jobs-list">
            <!-- بداية حلقة الوظائف: $jobs قادمة من SiteController@careers (مُدارة من لوحة التحكم: الوظائف) -->
            @forelse($jobs as $job)
                <div id="job-{{ $job->id }}" class="job-card reveal">
                    <div class="job-header">
                        <div>
                            <h3>{{ $job->title }}</h3>
                            <p class="job-location"><i class="fas fa-map-marker-alt"></i> {{ $job->location ?: 'غير محدد' }}</p>
                        </div>
                        <span class="job-type">{{ $job->type ?: 'دوام كامل' }}</span>
                    </div>
                    <p class="job-desc">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 180) }}</p>
                    <a href="{{ route('careers.apply', $job->id) }}" class="job-apply"><span>تقديم الطلب</span></a>
                </div>
            @empty
                <p>لا توجد وظائف متاحة حالياً.</p>
            @endforelse
            <!-- نهاية حلقة الوظائف -->
        </div>
        <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
        <div style="margin-top: 20px;">{{ $jobs->links() }}</div>
    </div>
</section>
@endsection
