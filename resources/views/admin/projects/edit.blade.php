@extends('admin.layouts.app')

@section('title', 'تعديل مشروع')
@section('page_title', 'تعديل مشروع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">المشاريع</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-warning">
    <div class="card-header"><h3 class="card-title">تعديل بيانات المشروع</h3></div>
    <!--
        خريطة تدفق البيانات (تعديل مشروع):
        - التحديث يتم عبر Admin\ProjectController@update.
        - تغيير service_id يغير تجميع المشروع ضمن خدمة مختلفة في الواجهة.
        - رفع صورة جديدة يستبدل السابقة (ويقوم المتحكم بحذف القديمة من التخزين).
        - is_published يتحكم في ظهور المشروع ضمن صفحات الواجهة.
    -->
    <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>اسم المشروع</label>
                @if(!empty($enableMultilingual))
                    <input type="text" name="title[ar]" class="form-control mb-2" value="{{ old('title.ar', $project->getTranslation('title','ar')) }}" required>
                    <input type="text" name="title[en]" class="form-control" value="{{ old('title.en', $project->getTranslation('title','en')) }}">
                @else
                    <input type="text" name="title" class="form-control" value="{{ old('title', $project->getTranslation('title','ar') ?: $project->title) }}" required>
                @endif
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الخدمة</label>
                        <select name="service_id" class="form-control" required>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ (int) old('service_id', $project->service_id) === $service->id ? 'selected' : '' }}>{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>العميل (اختياري)</label>
                        <select name="client_id" class="form-control">
                            <option value="">— بدون عميل —</option>
                            @forelse($clients as $client)
                                <option value="{{ $client->id }}" {{ (int) old('client_id', $project->client_id) === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @empty
                                <option value="" disabled>لا توجد قائمة عملاء (تأكد من تشغيل ترحيلات قاعدة البيانات)</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>التصنيف</label>
                        @if(!empty($enableMultilingual))
                            <input type="text" name="category[ar]" class="form-control mb-2" value="{{ old('category.ar', $project->getTranslation('category','ar')) }}">
                            <input type="text" name="category[en]" class="form-control" value="{{ old('category.en', $project->getTranslation('category','en')) }}">
                        @else
                            <input type="text" name="category" class="form-control" value="{{ old('category', $project->getTranslation('category','ar') ?: $project->category) }}">
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الموقع</label>
                        @if(!empty($enableMultilingual))
                            <input type="text" name="location[ar]" class="form-control mb-2" value="{{ old('location.ar', $project->getTranslation('location','ar')) }}">
                            <input type="text" name="location[en]" class="form-control" value="{{ old('location.en', $project->getTranslation('location','en')) }}">
                        @else
                            <input type="text" name="location" class="form-control" value="{{ old('location', $project->getTranslation('location','ar') ?: $project->location) }}">
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>تفاصيل المشروع</label>
                @if(!empty($enableMultilingual))
                    <textarea name="description[ar]" class="form-control js-editor mb-2" data-editor-context="projects" rows="6">{{ old('description.ar', $project->getTranslation('description','ar')) }}</textarea>
                    <textarea name="description[en]" class="form-control js-editor" data-editor-context="projects" rows="6">{{ old('description.en', $project->getTranslation('description','en')) }}</textarea>
                @else
                    <textarea name="description" class="form-control js-editor" data-editor-context="projects" rows="8">{{ old('description', $project->getTranslation('description','ar') ?: $project->description) }}</textarea>
                @endif
            </div>
            <div class="form-group">
                <label>صورة جديدة (اختياري)</label>
                <!-- رفع صورة بديلة للمشروع: عند الرفع سيتم حذف الصورة القديمة من التخزين العام -->
                <input type="file" name="image" class="form-control">
                <small class="text-muted d-block mt-1">المقاس المقترح: 1200×900 بكسل (نسبة 4:3) أو 1600×900 بكسل (نسبة 16:9)</small>
            </div>
            <div class="form-check">
                <!-- حالة النشر: تتحكم في ظهور/إخفاء المشروع للزوار -->
                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ old('is_published', $project->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">عرض المشروع في الموقع</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-warning">حفظ التعديلات</button>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-default">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    @include('admin.partials.summernote')
@endsection
