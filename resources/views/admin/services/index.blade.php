@extends('admin.layouts.app')

@section('title', 'إدارة الخدمات')
@section('page_title', 'قائمة الخدمات')

@section('breadcrumb')
    <li class="breadcrumb-item active">الخدمات</li>
@endsection

@section('content')
<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title">جميع الخدمات</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (قائمة الخدمات):
                - $services قادمة من Admin\ServiceController@index.
                - أي خدمة يتم نشرها هنا (is_published) ستظهر في واجهة الموقع ضمن صفحة الخدمات والصفحة الرئيسية.
            -->
            <form action="{{ route('admin.services.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.services.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> إضافة خدمة جديدة
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>العنوان</th>
                    <th>الصورة</th>
                    <th>الحالة</th>
                    <th style="width: 150px">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية حلقة الخدمات: تعرض بيانات جدول services كما أدخلتها الإدارة -->
                @forelse($services as $service)
                    <tr>
                        <td>{{ $service->id }}</td>
                        <td>{{ $service->title }}</td>
                        <td>{{ $service->image ? 'متوفر' : '-' }}</td>
                        <td>
                            <span class="badge {{ $service->is_published ? 'badge-success' : 'badge-secondary' }}">
                                {{ $service->is_published ? 'منشورة' : 'مسودة' }}
                            </span>
                        </td>
                        <td>
                            <!-- التعديل/الحذف: ينعكس مباشرة على الواجهة (مع مراعاة حالة النشر) -->
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد خدمات مضافة حالياً.</td>
                    </tr>
                @endforelse
                <!-- نهاية حلقة الخدمات -->
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
    <div class="card-footer">{{ $services->links() }}</div>
</div>
@endsection
