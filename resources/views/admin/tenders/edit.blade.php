@extends('admin.layouts.app')

@section('title', 'تعديل مناقصة')
@section('page_title', 'تعديل مناقصة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.tenders.index') }}">المناقصات</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-danger">
    <div class="card-header"><h3 class="card-title">تعديل بيانات المناقصة</h3></div>
    <!--
        خريطة تدفق البيانات (تعديل مناقصة):
        - التحديث يتم عبر Admin\TenderController@update.
        - تغيير closing_date/status/is_published ينعكس على صفحة المناقصات بالواجهة وعلى إمكانية التقديم.
        - نموذج التقديم بالواجهة يرسل الطلب إلى ContactRequestController@storeTenderRequest ويُحفظ ضمن contacts.
    -->
    <form action="{{ route('admin.tenders.update', $tender) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>العنوان</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $tender->title) }}" required>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>نوع العمل</label>
                        <input type="text" name="work_type" class="form-control" value="{{ old('work_type', $tender->work_type) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الموقع</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $tender->location) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>تاريخ الإغلاق</label>
                        <!-- الموعد النهائي لاستقبال العروض -->
                        <input type="datetime-local" name="closing_date" class="form-control" value="{{ old('closing_date', optional($tender->closing_date)->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>تفاصيل المناقصة</label>
                <textarea name="description" class="form-control js-editor" rows="8">{{ old('description', $tender->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>الحالة</label>
                <select name="status" class="form-control">
                    @foreach(['open' => 'مفتوحة', 'closed' => 'مغلقة', 'completed' => 'مكتملة'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $tender->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check">
                <!-- حالة النشر: تتحكم في ظهور المناقصة في الواجهة -->
                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ old('is_published', $tender->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">نشر المناقصة</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-danger">حفظ التعديلات</button>
            <a href="{{ route('admin.tenders.index') }}" class="btn btn-default">إلغاء</a>
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
            });
        })(jQuery);
    </script>
@endsection
