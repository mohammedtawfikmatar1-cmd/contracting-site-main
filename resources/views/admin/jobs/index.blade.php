@extends('admin.layouts.app')

@section('title', 'إدارة الوظائف')
@section('page_title', 'قائمة الوظائف')

@section('breadcrumb')
    <li class="breadcrumb-item active">الوظائف</li>
@endsection

@section('content')
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">الوظائف الشاغرة</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (قائمة الوظائف):
                - $jobs قادمة من Admin\JobController@index.
                - تفعيل الوظيفة (is_active) وتاريخ الإغلاق يتحكمان في ظهورها بالواجهة الأمامية ضمن صفحة الوظائف.
                - التقديم على الوظيفة من الواجهة يذهب إلى ContactRequestController@storeJobApplication ويُحفظ ضمن contacts.
            -->
            <form action="{{ route('admin.jobs.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.jobs.create') }}" class="btn btn-info btn-sm"><i class="fas fa-plus"></i> إضافة وظيفة</a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المسمى</th>
                    <th>الموقع</th>
                    <th>تاريخ الإغلاق</th>
                    <th>الحالة</th>
                    <th>عمليات</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية قائمة الوظائف: تعرض سجلات جدول job_posts عبر Model Job -->
                @forelse($jobs as $job)
                    <tr>
                        <td>{{ $job->id }}</td>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->location ?: '-' }}</td>
                        <td>{{ optional($job->closing_date)->format('Y-m-d') ?: '-' }}</td>
                        <td>{{ $job->is_active ? 'نشطة' : 'غير نشطة' }}</td>
                        <td>
                            <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.jobs.destroy', $job) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">لا توجد وظائف حالياً.</td></tr>
                @endforelse
                <!-- نهاية قائمة الوظائف -->
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() -->
    <div class="card-footer">{{ $jobs->links() }}</div>
</div>
@endsection
