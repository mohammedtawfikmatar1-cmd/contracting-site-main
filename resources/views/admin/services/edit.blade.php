@extends('admin.layouts.app')

@section('title', 'تعديل خدمة')
@section('page_title', 'تعديل خدمة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">الخدمات</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-success">
    <div class="card-header"><h3 class="card-title">تعديل بيانات الخدمة</h3></div>
    <!--
        خريطة تدفق البيانات (تعديل خدمة):
        - التحديث يتم عبر Admin\ServiceController@update.
        - رفع صورة جديدة يستبدل الصورة القديمة في التخزين (Storage) بحسب منطق المتحكم.
        - تغيير is_published ينعكس مباشرة على ظهور الخدمة في الواجهة.
        - تعديل العنوان قد يؤدي إلى إعادة توليد slug تلقائيا.
    -->
    <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>اسم الخدمة</label>
                @if(!empty($enableMultilingual))
                    <ul class="nav nav-tabs mb-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#svc-title-ar" role="tab">عربي</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#svc-title-en" role="tab">English</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="svc-title-ar" role="tabpanel">
                            <input type="text" name="title[ar]" class="form-control" value="{{ old('title.ar', $service->getTranslation('title','ar')) }}" required>
                        </div>
                        <div class="tab-pane fade" id="svc-title-en" role="tabpanel">
                            <input type="text" name="title[en]" class="form-control" value="{{ old('title.en', $service->getTranslation('title','en')) }}">
                        </div>
                    </div>
                @else
                    <input type="text" name="title" class="form-control" value="{{ old('title', is_array($service->getTranslations('title')) ? ($service->getTranslation('title','ar') ?: $service->title) : $service->title) }}" required>
                @endif
            </div>

            <div class="form-group">
                <label>وصف الخدمة</label>
                @if(!empty($enableMultilingual))
                    <ul class="nav nav-tabs mb-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#svc-overview-ar" role="tab">عربي</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#svc-overview-en" role="tab">English</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="svc-overview-ar" role="tabpanel">
                            <textarea name="overview[ar]" class="form-control" rows="3" maxlength="500">{{ old('overview.ar', $service->getTranslation('overview','ar')) }}</textarea>
                        </div>
                        <div class="tab-pane fade" id="svc-overview-en" role="tabpanel">
                            <textarea name="overview[en]" class="form-control" rows="3" maxlength="500">{{ old('overview.en', $service->getTranslation('overview','en')) }}</textarea>
                        </div>
                    </div>
                @else
                    <textarea name="overview" class="form-control" rows="3" maxlength="500">{{ old('overview', is_array($service->getTranslations('overview')) ? ($service->getTranslation('overview','ar') ?: $service->overview) : $service->overview) }}</textarea>
                @endif
            </div>

            <div class="form-group">
                <label>تفاصيل الخدمة</label>
                @if(!empty($enableMultilingual))
                    <ul class="nav nav-tabs mb-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#svc-desc-ar" role="tab">عربي</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#svc-desc-en" role="tab">English</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="svc-desc-ar" role="tabpanel">
                            <textarea name="description[ar]" class="form-control js-editor" data-editor-context="services" rows="8">{{ old('description.ar', $service->getTranslation('description','ar')) }}</textarea>
                        </div>
                        <div class="tab-pane fade" id="svc-desc-en" role="tabpanel">
                            <textarea name="description[en]" class="form-control js-editor" data-editor-context="services" rows="8">{{ old('description.en', $service->getTranslation('description','en')) }}</textarea>
                        </div>
                    </div>
                @else
                    <textarea name="description" class="form-control js-editor" data-editor-context="services" rows="8">{{ old('description', is_array($service->getTranslations('description')) ? ($service->getTranslation('description','ar') ?: $service->description) : $service->description) }}</textarea>
                @endif
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>الأيقونة</label>
                        <div class="input-group icon-picker-wrap">
                            <input type="text" name="icon" class="form-control icon-picker" value="{{ old('icon', $service->icon) }}" placeholder="مثال: fas fa-tools" autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary btn-icon-picker-open" title="فتح قائمة الأيقونات">
                                    <i class="fas fa-palette"></i>
                                </button>
                                <span class="input-group-text icon-picker-preview"><i class="{{ old('icon', $service->icon) ?: 'fas fa-icons' }}"></i></span>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">استخدم زر لوحة الألوان لفتح النافذة، أو اكتب صنف الأيقونة يدوياً.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>ترتيب الظهور</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $service->sort_order) }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>صورة جديدة (اختياري)</label>
                <!-- رفع صورة بديلة: عند الرفع يتم حذف الصورة القديمة من التخزين العام -->
                <input type="file" name="image" class="form-control">
                <small class="text-muted d-block mt-1">المقاس المقترح: 1200×800 بكسل (نسبة 3:2)</small>
            </div>
            <div class="form-check">
                <!-- حالة النشر: تتحكم في ظهور الخدمة في الموقع -->
                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ old('is_published', $service->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">عرض الخدمة في الموقع</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success">حفظ التعديلات</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-default">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    @include('admin.partials.summernote')
@endsection
