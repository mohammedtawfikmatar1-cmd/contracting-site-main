@extends('admin.layouts.app')

@section('title', 'إعدادات صفحة من نحن')
@section('page_title', 'إعدادات صفحة من نحن')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">إعدادات الموقع</a></li>
  <li class="breadcrumb-item active">صفحة من نحن</li>
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
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">تحكم كامل بمحتوى صفحة من نحن</h3>
        </div>

        <!--
          خريطة تدفق البيانات:
          - هذه الصفحة ترتبط مباشرة بواجهة route('about').
          - الحقول تُحفظ في جدول settings عبر SettingController@saveAboutPage.
          - الصورة تُعرض في about.blade، وإذا لم تُضف يظهر نص إرشادي بدل الصورة.
        -->
        <form action="{{ route('admin.settings.about.save') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <label>عنوان صفحة من نحن</label>
              <input type="text" name="about_title" class="form-control" value="{{ old('about_title', $settings['about_title'] ?? '') }}" placeholder="مثال: شركة مقاولات رائدة في تنفيذ المشاريع">
            </div>

            <div class="form-group">
              <label>النص التعريفي الأول</label>
              <textarea name="about_text_1" class="form-control js-editor" data-editor-context="settings_about" rows="5" placeholder="اكتب نصا تعريفيا موجزا عن الشركة">{{ old('about_text_1', $settings['about_text_1'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
              <label>النص التعريفي الثاني</label>
              <textarea name="about_text_2" class="form-control js-editor" data-editor-context="settings_about" rows="5" placeholder="اكتب نصا إضافيا عن الرؤية أو الرسالة">{{ old('about_text_2', $settings['about_text_2'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
              <label>صورة صفحة من نحن</label>
              <input type="file" name="about_main_image" class="form-control">
              <small class="text-muted d-block mt-1">المقاس المقترح: 1200×800 بكسل.</small>

              @if(!empty($settings['about_main_image']))
                <div class="mt-2">
                  <img src="{{ $settings['about_main_image'] }}" alt="about image" style="max-height:100px;">
                </div>
              @endif
            </div>
          </div>

          <div class="card-footer">
            <button class="btn btn-primary">حفظ</button>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">رجوع</a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  @include('admin.partials.summernote')
@endsection
