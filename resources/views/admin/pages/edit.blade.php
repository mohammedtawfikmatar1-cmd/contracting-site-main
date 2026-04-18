@extends('admin.layouts.app')

@section('title', 'تعديل صفحة')
@section('page_title', 'تعديل صفحة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">الصفحات</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<div class="card card-secondary">
    <div class="card-header"><h3 class="card-title">تعديل بيانات الصفحة</h3></div>
    <!--
        خريطة تدفق البيانات (تعديل صفحة):
        - التحديث يتم عبر Admin\PageController@update.
        - تعديل العنوان قد يؤدي إلى إعادة توليد slug تلقائيا حسب Trait HasUniqueSlug.
        - تغيير template يغير طريقة عرض الصفحة في الواجهة إذا كان القالب موجودا.
        - خيار is_published يتحكم في إظهار/إخفاء الصفحة في الواجهة الأمامية.
    -->
    <form action="{{ route('admin.pages.update', $page) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>العنوان</label>
                @if(!empty($enableMultilingual))
                    <!-- تعديل العنوان باللغتين -->
                    <input type="text" name="title[ar]" class="form-control mb-2" value="{{ old('title.ar', $page->getTranslation('title','ar')) }}" required>
                    <input type="text" name="title[en]" class="form-control" value="{{ old('title.en', $page->getTranslation('title','en')) }}">
                @else
                    <!-- تعديل العنوان بلغة واحدة -->
                    <input type="text" name="title" class="form-control" value="{{ old('title', $page->getTranslation('title','ar') ?: $page->title) }}" required>
                @endif
            </div>
            <div class="alert alert-light border">
                سيتم توليد الرابط (Slug) تلقائياً من عنوان الصفحة عند الحفظ.
            </div>
            <div class="form-group">
                <label>القالب</label>
                <input type="text" name="template" class="form-control" value="{{ old('template', $page->template) }}">
            </div>
            <div class="form-group">
                <label>المحتوى</label>
                @if(!empty($enableMultilingual))
                    <!-- تعديل المحتوى باللغتين -->
                    <textarea name="content[ar]" class="form-control mb-2" rows="10">{{ old('content.ar', $page->getTranslation('content','ar')) }}</textarea>
                    <textarea name="content[en]" class="form-control" rows="10">{{ old('content.en', $page->getTranslation('content','en')) }}</textarea>
                @else
                    <!-- تعديل المحتوى بلغة واحدة -->
                    <textarea name="content" class="form-control" rows="12">{{ old('content', $page->getTranslation('content','ar') ?: $page->content) }}</textarea>
                @endif
            </div>
            <div class="form-check">
                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">نشر الصفحة</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-secondary">حفظ التعديلات</button>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-default">إلغاء</a>
        </div>
    </form>
</div>
@endsection
