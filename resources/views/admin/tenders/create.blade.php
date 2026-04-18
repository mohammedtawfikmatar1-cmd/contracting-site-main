@extends('admin.layouts.app')

@section('title', 'إضافة مناقصة جديدة')
@section('page_title', 'إضافة مناقصة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.tenders.index') }}">المناقصات</a></li>
    <li class="breadcrumb-item active">إضافة مناقصة</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">بيانات المناقصة</h3>
            </div>
            <!--
                خريطة تدفق البيانات (إنشاء مناقصة):
                - الحفظ يتم عبر Admin\TenderController@store.
                - is_published يتحكم في ظهور المناقصة في الواجهة ضمن صفحة المناقصات.
                - closing_date + status يساعدان في تحديد ما إذا كانت المناقصة ما زالت مفتوحة للتقديم.
            -->
            <form role="form" action="{{ route('admin.tenders.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">عنوان المناقصة</label>
                                <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="أدخل عنوان المناقصة" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="work_type">نوع العمل</label>
                                <input type="text" name="work_type" class="form-control" id="work_type" value="{{ old('work_type') }}" placeholder="مثل: أعمال مدنية">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="closing_date">تاريخ الإغلاق</label>
                                <!-- الموعد النهائي لاستقبال العروض في الواجهة -->
                                <input type="datetime-local" name="closing_date" class="form-control" id="closing_date" value="{{ old('closing_date') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        سيتم توليد الرابط (Slug) تلقائياً من عنوان المناقصة عند الحفظ.
                    </div>
                    
                    <div class="form-group">
                        <label for="location">موقع العمل</label>
                        <input type="text" name="location" class="form-control" id="location" value="{{ old('location') }}" placeholder="أدخل موقع العمل">
                    </div>

                    <div class="form-group">
                        <label for="description">وصف المناقصة وشروطها</label>
                        <textarea name="description" class="form-control" id="description" rows="10" placeholder="أدخل تفاصيل وشروط المناقصة">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">حالة المناقصة</label>
                                <select name="status" class="form-control" id="status">
                                    <option value="open">مفتوحة</option>
                                    <option value="closed">مغلقة</option>
                                    <option value="completed">مكتملة</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4 pt-2">
                                <!-- نشر المناقصة: عند الإيقاف لا تظهر للزوار -->
                                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" checked>
                                <label class="form-check-label" for="is_published">نشر المناقصة في الموقع</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-danger">حفظ المناقصة</button>
                    <a href="{{ route('admin.tenders.index') }}" class="btn btn-default">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
