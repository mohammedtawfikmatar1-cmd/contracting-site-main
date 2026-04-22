@extends('admin.layouts.app')

@section('title', 'إضافة عميل')
@section('page_title', 'إضافة عميل جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">العملاء</a></li>
    <li class="breadcrumb-item active">إضافة</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">بيانات العميل</h3></div>
    <!--
        خريطة تدفق البيانات:
        - الحفظ عبر Admin\ClientController@store.
        - يجب اختيار مشروع واحد على الأقل ليتحقق شرط الربط في النظام.
        - الشعار يُخزن في storage ثم يُعرض في الشريط بالصفحة الرئيسية.
    -->
    <form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>اسم العميل</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label>نبذة عن العميل</label>
                <textarea name="description" class="form-control js-editor" rows="5" maxlength="5000">{{ old('description') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>شعار العميل</label>
                        <input type="file" name="logo" class="form-control" required accept="image/*">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>ترتيب العرض</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="d-block">نشر للزوار</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_published">إظهار في الموقع</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>المشاريع المرتبطة <span class="text-danger">*</span></label>
                <select name="project_ids[]" class="form-control" multiple size="12" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ collect(old('project_ids', []))->contains($project->id) ? 'selected' : '' }}>
                            {{ $project->title }}
                            @if($project->client_id)
                                (مرتبط حالياً بعميل آخر)
                            @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">استخدم Ctrl أو Shift لاختيار أكثر من مشروع. يجب اختيار مشروع واحد على الأقل.</small>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">حفظ</button>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">رجوع</a>
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
                    height: 200,
                    lang: 'ar-AR',
                    toolbar: [
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }

            $(function () {
                $('.js-editor').each(function () { initSummernote($(this)); });
            });
        })(jQuery);
    </script>
@endsection
