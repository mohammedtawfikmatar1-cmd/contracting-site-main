@extends('admin.layouts.app')

@section('title', 'تعديل خبر')
@section('page_title', 'تعديل خبر')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.news.index') }}">الأخبار</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">تعديل بيانات الخبر</h3></div>
    <!--
        خريطة تدفق البيانات (تعديل خبر):
        - التحديث يتم عبر Admin\NewsController@update.
        - تغيير is_published/published_at يحدد ما إذا كان الخبر يظهر في الواجهة ومتى يظهر.
        - رفع صورة جديدة يستبدل الصورة القديمة في التخزين العام.
    -->
    <form action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>العنوان</label>
                @if(!empty($enableMultilingual))
                    <input type="text" name="title[ar]" class="form-control mb-2" value="{{ old('title.ar', $news->getTranslation('title','ar')) }}" required>
                    <input type="text" name="title[en]" class="form-control" value="{{ old('title.en', $news->getTranslation('title','en')) }}">
                @else
                    <input type="text" name="title" class="form-control" value="{{ old('title', $news->getTranslation('title','ar') ?: $news->title) }}" required>
                @endif
            </div>
   
            <div class="form-group">
                <label>التصنيف</label>
                @if(!empty($enableMultilingual))
                    <input type="text" name="category[ar]" class="form-control mb-2" value="{{ old('category.ar', $news->getTranslation('category','ar')) }}">
                    <input type="text" name="category[en]" class="form-control" value="{{ old('category.en', $news->getTranslation('category','en')) }}">
                @else
                    <input type="text" name="category" class="form-control" value="{{ old('category', $news->getTranslation('category','ar') ?: $news->category) }}">
                @endif
            </div>
            <div class="form-group">
                <label>المحتوى</label>
                @if(!empty($enableMultilingual))
                    <textarea name="content[ar]" class="form-control js-editor mb-2" rows="8">{{ old('content.ar', $news->getTranslation('content','ar')) }}</textarea>
                    <textarea name="content[en]" class="form-control js-editor" rows="8">{{ old('content.en', $news->getTranslation('content','en')) }}</textarea>
                @else
                    <textarea name="content" class="form-control js-editor" rows="10">{{ old('content', $news->getTranslation('content','ar') ?: $news->content) }}</textarea>
                @endif
            </div>
            <div class="form-group">
                <label>تاريخ النشر</label>
                <!-- التاريخ المستخدم في فلترة الأخبار المنشورة في الواجهة -->
                <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($news->published_at)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="form-group">
                <label>صورة جديدة (اختياري)</label>
                <!-- رفع صورة بديلة للخبر: يتم حذف القديمة من التخزين في المتحكم -->
                <input type="file" name="image" class="form-control">
                <small class="text-muted d-block mt-1">المقاس المقترح: 1200×675 بكسل (نسبة 16:9)</small>
            </div>
            <div class="form-check">
                <!-- حالة النشر: تتحكم في ظهور الخبر للزوار -->
                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ old('is_published', $news->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">نشر الخبر</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">حفظ التعديلات</button>
            <a href="{{ route('admin.news.index') }}" class="btn btn-default">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('public/admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('public/admin/plugins/summernote/lang/summernote-ar-AR.js') }}"></script>
    <script>
        (function ($) {
            function initSummernote($el) {
                if (!$el.length || $el.data('summernote')) return;
                $el.summernote({
                    height: 260,
                    lang: 'ar-AR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'table']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }

            $(function () {
                $('.js-editor').each(function () { initSummernote($(this)); });
                $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                    $('.js-editor').each(function () { initSummernote($(this)); });
                });
            });
        })(jQuery);
    </script>
@endsection
