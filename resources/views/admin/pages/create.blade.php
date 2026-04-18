@extends('admin.layouts.app')

@section('title', 'إضافة صفحة جديدة')
@section('page_title', 'إنشاء صفحة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">الصفحات</a></li>
    <li class="breadcrumb-item active">إضافة صفحة</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">بيانات الصفحة</h3>
            </div>
            <!--
                خريطة تدفق البيانات (إنشاء صفحة):
                - يتم الحفظ عبر Admin\PageController@store.
                - يتم توليد slug تلقائيا (HasUniqueSlug) اعتمادا على العنوان.
                - عند النشر ستظهر الصفحة في الواجهة عبر SiteController@page.
                - عند تفعيل تعدد اللغات من الإعدادات، تظهر حقول ar/en وتُخزن وفق HasTranslations.
            -->
            <form role="form" action="{{ route('admin.pages.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">عنوان الصفحة</label>
                        @if(!empty($enableMultilingual))
                            <!-- حقول العنوان متعددة اللغة -->
                            <input type="text" name="title[ar]" class="form-control mb-2" value="{{ old('title.ar') }}" placeholder="عنوان الصفحة (عربي)" required>
                            <input type="text" name="title[en]" class="form-control" value="{{ old('title.en') }}" placeholder="Page title (EN)">
                        @else
                            <!-- عنوان بلغة واحدة -->
                            <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="أدخل عنوان الصفحة" required>
                        @endif
                    </div>

                    <div class="alert alert-light border">
                        سيتم توليد الرابط (Slug) تلقائياً من عنوان الصفحة عند الحفظ.
                    </div>
                    
                    <div class="form-group">
                        <label for="template">قالب الصفحة</label>
                        <!-- القالب يتحكم في اختيار view داخل SiteController@page عند توفر ملف مطابق -->
                        <select name="template" class="form-control" id="template">
                            <option value="default">القالب الافتراضي</option>
                            <option value="about">من نحن</option>
                            <option value="contact">اتصل بنا</option>
                            <option value="full-width">عرض كامل</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">محتوى الصفحة</label>
                        @if(!empty($enableMultilingual))
                            <!-- محتوى متعدد اللغة -->
                            <textarea name="content[ar]" class="form-control mb-2" rows="10" placeholder="المحتوى (عربي)">{{ old('content.ar') }}</textarea>
                            <textarea name="content[en]" class="form-control" rows="10" placeholder="Content (EN)">{{ old('content.en') }}</textarea>
                        @else
                            <!-- محتوى بلغة واحدة -->
                            <textarea name="content" class="form-control" id="content" rows="15" placeholder="أدخل محتوى الصفحة (HTML/Text)">{{ old('content') }}</textarea>
                        @endif
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" checked>
                        <label class="form-check-label" for="is_published">نشر الصفحة</label>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-secondary">حفظ الصفحة</button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-default">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
