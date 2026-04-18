@extends('admin.layouts.app')

@section('title', 'إدارة المشاريع')
@section('page_title', 'قائمة المشاريع')

@section('breadcrumb')
    <li class="breadcrumb-item active">المشاريع</li>
@endsection

@section('content')
<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">جميع المشاريع</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (قائمة المشاريع):
                - $projects قادمة من Admin\ProjectController@index مع تحميل service عند الحاجة.
                - نشر المشروع (is_published) هو ما يتحكم في ظهوره بواجهة الموقع ضمن صفحة المشاريع والتفاصيل.
            -->
            <form action="{{ route('admin.projects.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.projects.create') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-plus"></i> إضافة مشروع جديد
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>اسم المشروع</th>
                    <th>التصنيف</th>
                    <th>الموقع</th>
                    <th>الحالة</th>
                    <th style="width: 150px">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية حلقة المشاريع: تعرض بيانات جدول projects كما أدخلتها الإدارة -->
                @forelse($projects as $project)
                    <tr>
                        <td>{{ $project->id }}</td>
                        <td>{{ $project->title }}</td>
                        <td>{{ $project->category ?: '-' }}</td>
                        <td>{{ $project->location ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $project->is_published ? 'badge-success' : 'badge-secondary' }}">
                                {{ $project->is_published ? 'منشور' : 'مسودة' }}
                            </span>
                        </td>
                        <td>
                            <!-- تعديل/حذف المشروع: ينعكس على الواجهة الأمامية مباشرة بعد الحفظ -->
                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد مشاريع مضافة حالياً.</td>
                    </tr>
                @endforelse
                <!-- نهاية حلقة المشاريع -->
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
    <div class="card-footer">{{ $projects->links() }}</div>
</div>
@endsection
