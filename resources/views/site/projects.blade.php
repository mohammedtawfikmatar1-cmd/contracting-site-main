@extends('site.layouts.app')

@section('title', 'شركة مقاولات - أعمالنا')

@section('styles')
@vite(['resources/css/projects.css'])
@endsection

@section('content')
<section id="projects" class="projects section-py">
    <div class="container">
        <div class="sec-head reveal">
            <span class="sec-label">أعمالنا</span>
            <h2>مشاريعنا</h2>
        </div>
        <div class="proj-grid">
            <!-- بداية حلقة المشاريع: $projects قادمة من SiteController@projects (مُدارة من لوحة التحكم: المشاريع) -->
            @forelse($projects as $project)
                <a class="proj-card reveal" href="{{ route('projects.details', $project->slug) }}">
                    <div class="proj-img"><img src="{{ $project->image_url ?: asset('imag/m1.jpg') }}" alt="{{ $project->title }}" loading="lazy"></div>
                    <div class="proj-info">
                        <span class="proj-tag">{{ $project->category ?: 'مشروع' }}</span>
                        <h4>{{ $project->title }}</h4>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($project->description), 90) }}</p>
                    </div>
                </a>
            @empty
                <p>لا توجد مشاريع منشورة حالياً.</p>
            @endforelse
            <!-- نهاية حلقة المشاريع -->
        </div>
        <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
        <div style="margin-top: 20px;">{{ $projects->links() }}</div>
    </div>
</section>
@endsection
