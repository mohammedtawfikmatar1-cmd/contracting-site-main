@extends('admin.layouts.app')

@section('title', 'إعدادات الموقع')
@section('page_title', 'إعدادات الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item active">إعدادات الموقع</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <!--
            خريطة تدفق البيانات (إعدادات الموقع):
            - هذه الشاشة تُدار من Admin\SettingController.
            - التغييرات في settings تنعكس في الواجهة الأمامية عبر SiteController@siteSettings
              وضمن قوالب الواجهة مثل: site/layouts/app (الألوان/الفافيكون) + header/footer (بيانات الشركة).
        -->
        <a href="{{ route('admin.settings.branding') }}" class="btn btn-info">
            <i class="fas fa-palette"></i> الهوية البصرية ومعلومات الشركة
        </a>
        <a href="{{ route('admin.settings.about') }}" class="btn btn-primary">
            <i class="fas fa-id-card"></i> صفحة من نحن
        </a>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header"><h3 class="card-title">إضافة إعداد جديد</h3></div>
            <!-- نموذج إضافة إعداد جديد: يُنشئ record جديد في جدول settings -->
            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>المفتاح</label>
                        <input type="text" name="key" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>القيمة</label>
                        <textarea name="value" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>النوع</label>
                        <select name="type" class="form-control">
                            <option value="text">text</option>
                            <option value="longtext">longtext</option>
                            <option value="image">image</option>
                            <option value="color">color</option>
                            <option value="json">json</option>
                            <option value="boolean">boolean</option>
                            <option value="integer">integer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الوصف</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <div class="card-footer"><button class="btn btn-info">حفظ</button></div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">جميع الإعدادات</h3></div>
            <div class="card-body p-0">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>المفتاح</th>
                            <th>القيمة</th>
                            <th>النوع</th>
                            <th>عمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- بداية قائمة الإعدادات: القيم قادمة من Admin\SettingController@index -->
                        @forelse($settings as $setting)
                            <tr>
                                <td>
                                    <!-- تحديث سريع: تعديل نفس السجل مباشرة (PUT) -->
                                    <form action="{{ route('admin.settings.update', $setting) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="key" class="form-control form-control-sm" value="{{ $setting->key }}" required>
                                </td>
                                <td style="max-width: 260px; word-break: break-word;">
                                        <input type="text" name="value" class="form-control form-control-sm" value="{{ $setting->value }}">
                                </td>
                                <td>
                                        <select name="type" class="form-control form-control-sm">
                                            @foreach(['text','longtext','image','color','json','boolean','integer'] as $type)
                                                <option value="{{ $type }}" {{ $setting->type === $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="description" class="form-control form-control-sm mt-1" value="{{ $setting->description }}" placeholder="وصف">
                                </td>
                                <td>
                                        <button class="btn btn-sm btn-primary">تحديث سريع</button>
                                    </form>
                                    <!-- حذف إعداد: قد يؤثر على الواجهة إذا كانت تستخدم هذا المفتاح -->
                                    <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">لا توجد إعدادات.</td></tr>
                        @endforelse
                        <!-- نهاية قائمة الإعدادات -->
                    </tbody>
                </table>
            </div>
            <!-- ترقيم الصفحات: ناتج paginate() -->
            <div class="card-footer">{{ $settings->links() }}</div>
        </div>
    </div>
</div>
@endsection
