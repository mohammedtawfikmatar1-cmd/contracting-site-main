@extends('site.layouts.app')

@section('title', 'عملاؤنا')

@section('styles')
@vite(['resources/css/index.css'])
@endsection

@section('content')
<section class="section-py" aria-labelledby="clients-page-title">
    <div class="container">
        <!--
            خريطة تدفق البيانات:
            - $clients من SiteController@clients (جدول clients + ربط المشاريع من الإدارة).
            - تظهر الصفحة فقط عند تفعيل clients_page_enabled من لوحة التحكم > العملاء.
        -->
        <div class="sec-head reveal">
            <span class="sec-label">شركاؤنا في النجاح</span>
            <h2 id="clients-page-title">عملاؤنا</h2>
            <p class="text-muted" style="max-width: 720px;">
                يعرض هذا القسم جهات عملنا المرتبطة بمشاريع منشورة في الموقع، ويُحدَّث من لوحة التحكم ضمن إدارة المحتوى.
            </p>
        </div>

        <div class="row">
            @forelse($clients as $client)
                <div class="col-md-6 col-lg-4 mb-4 reveal" id="c-{{ $client->slug }}">
                    <article class="card h-100 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            @if($client->logo_url)
                                <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" style="max-height: 72px; object-fit: contain;" loading="lazy">
                            @endif
                            <h3 class="h5 mt-3 mb-2">{{ $client->name }}</h3>
                            @if(filled($client->description))
                                <p class="text-muted small flex-grow-1">{{ \Illuminate\Support\Str::limit(strip_tags($client->description), 220) }}</p>
                            @endif
                            @if($client->projects->isNotEmpty())
                                <div class="w-100 mt-2">
                                    <span class="sec-label d-block mb-2">مشاريع مرتبطة</span>
                                    <ul class="list-unstyled small text-right mb-0" style="line-height: 1.9;">
                                        @foreach($client->projects as $proj)
                                            <li>
                                                <a href="{{ route('projects.details', $proj->slug) }}">{{ $proj->title }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </article>
                </div>
            @empty
                <p class="col-12 text-center text-muted">لا يوجد عملاء منشورون مرتبطون بمشاريع حالياً.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
