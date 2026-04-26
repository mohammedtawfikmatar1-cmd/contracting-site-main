@extends('admin.layouts.app')

@section('title', 'إضافة خبر جديد')
@section('page_title', 'إضافة خبر جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.news.index') }}">الأخبار</a></li>
    <li class="breadcrumb-item active">إضافة خبر</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">بيانات الخبر</h3>
            </div>
            <!--
                خريطة تدفق البيانات (إنشاء خبر):
                - الحفظ يتم عبر Admin\NewsController@store.
                - is_published + published_at يحددان ظهور الخبر في الواجهة (صفحة الأخبار + الرئيسية).
                - رفع الصورة ينعكس في بطاقات الأخبار وصفحة تفاصيل الخبر.
                - عند تفعيل تعدد اللغات، يتم حفظ title/content/category بصيغة ar/en.
            -->
            <form role="form" action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">عنوان الخبر</label>
                        @if(!empty($enableMultilingual))
                            <input type="text" name="title[ar]" class="form-control mb-2" value="{{ old('title.ar') }}" placeholder="عنوان الخبر (عربي)" required>
                            <input type="text" name="title[en]" class="form-control" value="{{ old('title.en') }}" placeholder="News title (EN)">
                        @else
                            <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="أدخل عنوان الخبر" required>
                        @endif
                    </div>

                    
                    <div class="form-group">
                        <label for="category">التصنيف</label>
                        @if(!empty($enableMultilingual))
                            <input type="text" name="category[ar]" class="form-control mb-2" value="{{ old('category.ar') }}" placeholder="التصنيف (عربي)">
                            <input type="text" name="category[en]" class="form-control" value="{{ old('category.en') }}" placeholder="Category (EN)">
                        @else
                            <input type="text" name="category" class="form-control" id="category" value="{{ old('category') }}" placeholder="أدخل التصنيف">
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="content">محتوى الخبر</label>
                        @if(!empty($enableMultilingual))
                            <textarea name="content[ar]" class="form-control js-editor mb-2" data-editor-context="news" rows="8" placeholder="محتوى الخبر (عربي)">{{ old('content.ar') }}</textarea>
                            <textarea name="content[en]" class="form-control js-editor" data-editor-context="news" rows="8" placeholder="News content (EN)">{{ old('content.en') }}</textarea>
                        @else
                            <textarea name="content" class="form-control js-editor" data-editor-context="news" id="content" rows="10" placeholder="أدخل محتوى الخبر">{{ old('content') }}</textarea>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="published_at">تاريخ ووقت النشر</label>
                        <!-- إذا تُرك فارغا مع تفعيل النشر قد يتم ضبطه تلقائيا داخل Model News أثناء الحفظ -->
                        <input type="datetime-local" name="published_at" class="form-control" id="published_at" value="{{ old('published_at') }}">
                    </div>

                    <div class="form-group">
                        <label for="image">صورة الخبر</label>
                        <!-- رفع صورة الخبر: تُخزن في storage/public وتُعرض في الواجهة عبر image_url -->
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input" id="image">
                                <label class="custom-file-label" for="image">اختر ملفاً</label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">المقاس المقترح: 1200×675 بكسل (نسبة 16:9)</small>
                    </div>

                    <div class="form-check">
                        <!-- نشر الخبر: يتحكم في ظهوره للزوار -->
                        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" checked>
                        <label class="form-check-label" for="is_published">نشر الخبر فوراً</label>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">حفظ الخبر</button>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-default">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('admin.partials.summernote')
@endsection
