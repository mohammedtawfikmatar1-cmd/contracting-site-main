@extends('admin.layouts.app')

@section('title', 'إضافة مشروع جديد')
@section('page_title', 'إضافة مشروع جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">المشاريع</a></li>
    <li class="breadcrumb-item active">إضافة مشروع</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">بيانات المشروع</h3>
            </div>
            <!--
                خريطة تدفق البيانات (إنشاء مشروع):
                - الحفظ يتم عبر Admin\ProjectController@store.
                - project مرتبط بخدمة عبر service_id (يُحدد التصنيف الرئيسي للمشروع في الواجهة).
                - رفع الصورة الرئيسية ينعكس في بطاقة المشروع وصفحة التفاصيل بالواجهة.
                - is_published يتحكم في ظهور المشروع للزوار.
                - عند تفعيل تعدد اللغات، تُخزن الحقول نصيا بصيغة ar/en.
            -->
            <form role="form" action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">اسم المشروع</label>
                                @if(!empty($enableMultilingual))
                                    <input type="text" name="title[ar]" class="form-control mb-2" value="{{ old('title.ar') }}" placeholder="اسم المشروع (عربي)" required>
                                    <input type="text" name="title[en]" class="form-control" value="{{ old('title.en') }}" placeholder="Project name (EN)">
                                @else
                                    <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="أدخل اسم المشروع" required>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="service_id">الخدمة المرتبطة</label>
                                <select name="service_id" id="service_id" class="form-control" required>
                                    <option value="">اختر خدمة</option>
                                    <!-- بداية خيارات الخدمات: القيم قادمة من المتحكم ومُدارة من قسم الخدمات -->
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->title }}</option>
                                    @endforeach
                                    <!-- نهاية خيارات الخدمات -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category">التصنيف</label>
                                @if(!empty($enableMultilingual))
                                    <input type="text" name="category[ar]" class="form-control mb-2" value="{{ old('category.ar') }}" placeholder="التصنيف (عربي)">
                                    <input type="text" name="category[en]" class="form-control" value="{{ old('category.en') }}" placeholder="Category (EN)">
                                @else
                                    <input type="text" name="category" class="form-control" id="category" value="{{ old('category') }}" placeholder="مثل: سكني، تجاري">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="location">الموقع</label>
                                @if(!empty($enableMultilingual))
                                    <input type="text" name="location[ar]" class="form-control mb-2" value="{{ old('location.ar') }}" placeholder="الموقع (عربي)">
                                    <input type="text" name="location[en]" class="form-control" value="{{ old('location.en') }}" placeholder="Location (EN)">
                                @else
                                    <input type="text" name="location" class="form-control" id="location" value="{{ old('location') }}" placeholder="أدخل موقع المشروع">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!--
                        العميل (اختياري): client_id يُخزّن في جدول projects ويُدار أيضا من شاشة العملاء.
                        قد يبقى المشروع بدون عميل وفق سياسة المحتوى.
                    -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id">العميل (اختياري)</label>
                                <select name="client_id" id="client_id" class="form-control">
                                    <option value="">— بدون عميل —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (int) old('client_id') === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">يُستخدم لربط المشروع بشعار عميل في أقسام العملاء بالواجهة.</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        سيتم توليد الرابط (Slug) تلقائياً من اسم المشروع عند الحفظ.
                    </div>
                    
                    <div class="form-group">
                        <label for="description">وصف المشروع</label>
                        @if(!empty($enableMultilingual))
                            <textarea name="description[ar]" class="form-control mb-2" rows="6" placeholder="وصف المشروع (عربي)">{{ old('description.ar') }}</textarea>
                            <textarea name="description[en]" class="form-control" rows="6" placeholder="Project description (EN)">{{ old('description.en') }}</textarea>
                        @else
                            <textarea name="description" class="form-control" id="description" rows="8" placeholder="أدخل تفاصيل المشروع">{{ old('description') }}</textarea>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="image">صورة المشروع الرئيسية</label>
                        <!-- رفع صورة المشروع: تُخزن في storage/public وتُستخدم في الواجهة عبر image_url -->
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input" id="image">
                                <label class="custom-file-label" for="image">اختر صورة</label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">المقاس المقترح: 1200×900 بكسل (نسبة 4:3) أو 1600×900 بكسل (نسبة 16:9)</small>
                    </div>

                    <div class="form-check">
                        <!-- نشر المشروع: عند الإيقاف لا يظهر في صفحات المشاريع بالواجهة -->
                        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" checked>
                        <label class="form-check-label" for="is_published">عرض المشروع في الموقع</label>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">حفظ المشروع</button>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-default">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
