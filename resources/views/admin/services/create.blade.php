@extends('admin.layouts.app')

@section('title', 'إضافة خدمة جديدة')
@section('page_title', 'إضافة خدمة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">الخدمات</a></li>
    <li class="breadcrumb-item active">إضافة خدمة</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">بيانات الخدمة</h3>
            </div>
            <!--
                خريطة تدفق البيانات (إنشاء خدمة):
                - الحفظ يتم عبر Admin\ServiceController@store.
                - يتم توليد slug تلقائيا من العنوان (HasUniqueSlug).
                - رفع الصورة هنا ينعكس في واجهة الموقع ضمن صفحة الخدمات وتفاصيل الخدمة.
                - خيار is_published هو المفتاح لظهور الخدمة في الواجهة الأمامية.
                - عند تفعيل تعدد اللغات من الإعدادات، تُخزن القيم بصيغة ar/en وتُعرض في الواجهة حسب لغة الجلسة.
            -->
            <form role="form" action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">اسم الخدمة</label>
                        @if(!empty($enableMultilingual))
                            <!-- بداية إدخال الاسم متعدد اللغة -->
                            <ul class="nav nav-tabs mb-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#svc-title-ar" role="tab">عربي</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#svc-title-en" role="tab">English</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="svc-title-ar" role="tabpanel">
                                    <input type="text" name="title[ar]" class="form-control" value="{{ old('title.ar') }}" placeholder="أدخل اسم الخدمة" required>
                                </div>
                                <div class="tab-pane fade" id="svc-title-en" role="tabpanel">
                                    <input type="text" name="title[en]" class="form-control" value="{{ old('title.en') }}" placeholder="Service name (EN)">
                                </div>
                            </div>
                            <!-- نهاية إدخال الاسم متعدد اللغة -->
                        @else
                            <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="أدخل اسم الخدمة" required>
                        @endif
                    </div>

                    <div class="alert alert-light border">
                        سيتم توليد الرابط (Slug) تلقائياً من اسم الخدمة عند الحفظ.
                    </div>
                    
                    <div class="form-group">
                        <label for="description">وصف الخدمة</label>
                        @if(!empty($enableMultilingual))
                            <!-- بداية إدخال الوصف متعدد اللغة -->
                            <ul class="nav nav-tabs mb-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#svc-desc-ar" role="tab">عربي</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#svc-desc-en" role="tab">English</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="svc-desc-ar" role="tabpanel">
                                    <textarea name="description[ar]" class="form-control" rows="8" placeholder="أدخل وصفاً تفصيلياً للخدمة">{{ old('description.ar') }}</textarea>
                                </div>
                                <div class="tab-pane fade" id="svc-desc-en" role="tabpanel">
                                    <textarea name="description[en]" class="form-control" rows="8" placeholder="Service description (EN)">{{ old('description.en') }}</textarea>
                                </div>
                            </div>
                            <!-- نهاية إدخال الوصف متعدد اللغة -->
                        @else
                            <textarea name="description" class="form-control" id="description" rows="10" placeholder="أدخل وصفاً تفصيلياً للخدمة">{{ old('description') }}</textarea>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">الأيقونة (Font Awesome)</label>
                                <div class="input-group">
                                    <input type="text" name="icon" class="form-control icon-picker" id="icon" value="{{ old('icon') }}" placeholder="اختر أيقونة">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="{{ old('icon') ?: 'fas fa-icons' }}"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_order">ترتيب الظهور</label>
                                <input type="number" name="sort_order" class="form-control" id="sort_order" value="{{ old('sort_order', 0) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">صورة الخدمة</label>
                        <!-- رفع صورة الخدمة: تُخزن في storage/public ثم تُستخدم في الواجهة عبر image_url -->
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input" id="image">
                                <label class="custom-file-label" for="image">اختر صورة</label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">المقاس المقترح: 1200×800 بكسل (نسبة 3:2)</small>
                    </div>

                    <div class="form-check">
                        <!-- نشر الخدمة: عند الإيقاف ستبقى الخدمة مخزنة لكنها لا تظهر للزوار -->
                        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" checked>
                        <label class="form-check-label" for="is_published">عرض الخدمة في الموقع</label>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">حفظ الخدمة</button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-default">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
