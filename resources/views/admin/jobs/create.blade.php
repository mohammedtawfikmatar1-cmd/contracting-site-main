@extends('admin.layouts.app')

@section('title', 'إضافة وظيفة')
@section('page_title', 'إضافة وظيفة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">الوظائف</a></li>
    <li class="breadcrumb-item active">إضافة</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-info">
    <div class="card-header"><h3 class="card-title">بيانات الوظيفة</h3></div>
    <!--
        خريطة تدفق البيانات (إنشاء وظيفة):
        - الحفظ يتم عبر Admin\JobController@store.
        - requirements/skills تُدخل كنص متعدد الأسطر ثم يحولها المتحكم إلى مصفوفات JSON.
        - is_active + closing_date يتحكمان في ظهور الوظيفة بالواجهة الأمامية (صفحة الوظائف).
        - طلبات التقديم على الوظائف تأتي من الواجهة (SiteController@jobApply) وتُحفظ ضمن contacts.
    -->
    <form action="{{ route('admin.jobs.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>المسمى الوظيفي</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
          
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>الموقع</label><input type="text" name="location" class="form-control" value="{{ old('location') }}"></div></div>
                <div class="col-md-6"><div class="form-group"><label>نوع الوظيفة</label><input type="text" name="type" class="form-control" value="{{ old('type') }}"></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>الخبرة</label><input type="text" name="experience" class="form-control" value="{{ old('experience') }}"></div></div>
                <div class="col-md-6"><div class="form-group"><label>المؤهل</label><input type="text" name="qualification" class="form-control" value="{{ old('qualification') }}"></div></div>
            </div>
            <div class="form-group">
                <label>تفاصيل الوظيفة</label>
                <textarea name="description" class="form-control js-editor" rows="6">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label>المتطلبات (كل سطر عنصر)</label>
                <!-- سيتم تحويل كل سطر إلى عنصر داخل مصفوفة requirements عند الحفظ -->
                <textarea name="requirements" class="form-control" rows="4">{{ old('requirements') }}</textarea>
            </div>
            <div class="form-group">
                <label>المهارات (كل سطر عنصر)</label>
                <!-- سيتم تحويل كل سطر إلى عنصر داخل مصفوفة skills عند الحفظ -->
                <textarea name="skills" class="form-control" rows="4">{{ old('skills') }}</textarea>
            </div>
            <div class="form-group">
                <label>تاريخ الإغلاق</label>
                <input type="date" name="closing_date" class="form-control" value="{{ old('closing_date') }}">
            </div>
            <div class="form-check">
                <!-- حالة النشاط: تتحكم في ظهور الوظيفة للزوار -->
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">وظيفة نشطة</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-info">حفظ</button>
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-default">إلغاء</a>
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
                    height: 220,
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
            });
        })(jQuery);
    </script>
@endsection
