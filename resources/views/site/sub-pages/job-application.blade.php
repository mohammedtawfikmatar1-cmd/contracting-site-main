@extends('site.layouts.app')

@section('title', 'شركة مقاولات - طلب وظيفة')

@section('styles')
    @vite(['resources/css/service-details.css'])
    <style>
        .job-requirements {
            background: var(--d700);
            padding: var(--s6);
            border-radius: var(--r-xl);
            margin-bottom: var(--s7);
        }
        .job-requirements h3 { color: var(--orange); margin-bottom: var(--s4); }
        .job-requirements ul { list-style: none; }
        .job-requirements li { margin-bottom: var(--s3); display: flex; align-items: center; gap: var(--s3); color: var(--tm); }
        .job-requirements li i { color: var(--orange); font-size: 14px; }
    </style>
@endsection

@section('content')
<div class="service-details-page">
    <section class="service-hero">
        <div class="container">
            <span class="sec-label">انضم لفريقنا</span>
            <!-- بيانات الوظيفة: $job قادمة من SiteController@jobApply (مُدارة من لوحة التحكم: الوظائف) -->
            <h1>تقديم طلب توظيف - {{ $job->title }}</h1>
            <p>{{ $job->location ?: 'الموقع غير محدد' }}</p>
        </div>
    </section>

    <section class="section-py">
        <div class="container">
            <div class="service-content-grid">
                <div class="service-main-info reveal-up">
                    <div class="job-requirements">
                        <h3>متطلبات الوظيفة العامة</h3>
                        <ul>
                            <!-- بداية حلقة المتطلبات: requirements تُدار من لوحة التحكم داخل قسم الوظائف -->
                            @forelse(($job->requirements ?? []) as $requirement)
                                <li><i class="fas fa-check"></i> {{ $requirement }}</li>
                            @empty
                                <li><i class="fas fa-check"></i> {{ $job->qualification ?: 'يتم تحديد المتطلبات أثناء المقابلة.' }}</li>
                            @endforelse
                            <!-- نهاية حلقة المتطلبات -->
                        </ul>
                    </div>

                    <h2>لماذا تعمل معنا؟</h2>
                    <p class="service-description">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 220) }}</p>
                </div>

                <aside class="request-sidebar reveal-up" style="--delay: 200ms">
                    <div class="request-card">
                        <h3>نموذج التقديم</h3>
                        <!--
                            نموذج التقديم على وظيفة:
                            يرسل إلى route('careers.apply.store') => ContactRequestController@storeJobApplication
                            ثم يُحفظ الطلب في جدول contacts كـ career ويُرفع ملف cv_file ويظهر في لوحة التحكم.
                        -->
                        <form action="{{ route('careers.apply.store', $job) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="name">الاسم الكامل</label>
                                <input type="text" id="name" name="full_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">رقم الهاتف</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="message">نبذة مختصرة</label>
                                <textarea id="message" name="message" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="cv">السيرة الذاتية (PDF)</label>
                                <input type="file" id="cv" name="cv_file" class="form-control" accept=".pdf" required>
                            </div>
                            <button type="submit" class="cta-primary" style="width: 100%; justify-content: center;">
                                <span>إرسال الطلب</span>
                                <span class="cta-ico"><i class="fas fa-paper-plane"></i></span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
