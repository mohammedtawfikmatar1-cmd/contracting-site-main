@extends('admin.layouts.app')

@section('title', 'تعديل وظيفة')
@section('page_title', 'تعديل وظيفة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">الوظائف</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/admin/plugins/summernote/summernote-bs4.css') }}">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable { direction: rtl; text-align: right; }
    </style>
@endsection

@section('content')
<div class="card card-info">
    <div class="card-header"><h3 class="card-title">تعديل بيانات الوظيفة</h3></div>
    <!--
        خريطة تدفق البيانات (تعديل وظيفة):
        - التحديث يتم عبر Admin\JobController@update.
        - requirements/skills تُعرض هنا كنص متعدد الأسطر (implode) ثم يحولها المتحكم مجددا إلى مصفوفات عند الحفظ.
        - تعديل is_active/closing_date ينعكس فورا على ظهور الوظيفة بالواجهة.
    -->
    <form action="{{ route('admin.jobs.update', $job) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>المسمى الوظيفي</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $job->title) }}" required>
            </div>
      
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>الموقع</label><input type="text" name="location" class="form-control" value="{{ old('location', $job->location) }}"></div></div>
                <div class="col-md-6"><div class="form-group"><label>نوع الوظيفة</label><input type="text" name="type" class="form-control" value="{{ old('type', $job->type) }}"></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>الخبرة</label><input type="text" name="experience" class="form-control" value="{{ old('experience', $job->experience) }}"></div></div>
                <div class="col-md-6"><div class="form-group"><label>المؤهل</label><input type="text" name="qualification" class="form-control" value="{{ old('qualification', $job->qualification) }}"></div></div>
            </div>
            <div class="form-group">
                <label>تفاصيل الوظيفة</label>
                <textarea name="description" class="form-control js-editor" data-editor-context="jobs" rows="6">{{ old('description', $job->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>المتطلبات (كل سطر عنصر)</label>
                <!-- يتم تحويل النص إلى مصفوفة requirements عند الحفظ -->
                <textarea name="requirements" class="form-control" rows="4">{{ old('requirements', is_array($job->requirements) ? implode("\n", $job->requirements) : '') }}</textarea>
            </div>
            <div class="form-group">
                <label>المهارات (كل سطر عنصر)</label>
                <!-- يتم تحويل النص إلى مصفوفة skills عند الحفظ -->
                <textarea name="skills" class="form-control" rows="4">{{ old('skills', is_array($job->skills) ? implode("\n", $job->skills) : '') }}</textarea>
            </div>
            <div class="form-group">
                <label>تاريخ الإغلاق</label>
                <input type="date" name="closing_date" class="form-control" value="{{ old('closing_date', optional($job->closing_date)->format('Y-m-d')) }}">
            </div>
            <div class="form-check">
                <!-- حالة النشاط: تتحكم في ظهور الوظيفة للزوار -->
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $job->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">وظيفة نشطة</label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-info">حفظ التعديلات</button>
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-default">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    @include('admin.partials.summernote')
@endsection
