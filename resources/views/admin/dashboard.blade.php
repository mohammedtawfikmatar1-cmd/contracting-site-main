@extends('admin.layouts.app')

@section('title', 'الرئيسية')
@section('page_title', 'لوحة التحكم - نظرة عامة')

@section('breadcrumb')
    <li class="breadcrumb-item active">لوحة التحكم</li>
@endsection

@section('content')
<div class="row">
    <!--
        خريطة تدفق البيانات (لوحة القيادة):
        - $stats و $latestContacts و $latestProjects قادمة من Admin\DashboardController@index.
        - هذه البيانات تلخص ما تم إدخاله في أقسام الإدارة، وبالتالي تعكس حالة المحتوى الذي سيظهر في الواجهة الأمامية.
    -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['projects'] }}</h3>
                <p>المشاريع</p>
            </div>
            <div class="icon">
                <i class="fas fa-project-diagram"></i>
            </div>
            <a href="{{ route('admin.projects.index') }}" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['services'] }}</h3>
                <p>الخدمات</p>
            </div>
            <div class="icon">
                <i class="fas fa-concierge-bell"></i>
            </div>
            <a href="{{ route('admin.services.index') }}" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['new_messages'] }}</h3>
                <p>الرسائل الجديدة</p>
            </div>
            <div class="icon">
                <i class="fas fa-envelope"></i>
            </div>
            <a href="{{ route('admin.contacts.index') }}" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['active_tenders'] }}</h3>
                <p>المناقصات النشطة</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-contract"></i>
            </div>
            <a href="{{ route('admin.tenders.index') }}" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">آخر الرسائل</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <!-- بداية حلقة آخر الرسائل: مصدرها Contact (طلبات الواجهة الأمامية) وتظهر هنا للمتابعة -->
                    @forelse($latestContacts as $contact)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $contact->full_name }}</span>
                            <small class="text-muted">{{ $contact->created_at->format('Y-m-d') }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">لا توجد رسائل حالياً.</li>
                    @endforelse
                    <!-- نهاية حلقة آخر الرسائل -->
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">آخر المشاريع</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <!-- بداية حلقة آخر المشاريع: مصدرها Project (مُدار من قسم المشاريع) وتنعكس في الواجهة عند النشر -->
                    @forelse($latestProjects as $project)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $project->title }}</span>
                            <small class="text-muted">{{ $project->created_at->format('Y-m-d') }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">لا توجد مشاريع حالياً.</li>
                    @endforelse
                    <!-- نهاية حلقة آخر المشاريع -->
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
