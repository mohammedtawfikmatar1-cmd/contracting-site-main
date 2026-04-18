@extends('admin.layouts.app')

@section('title', 'إدارة المناقصات')
@section('page_title', 'قائمة المناقصات')

@section('breadcrumb')
    <li class="breadcrumb-item active">المناقصات</li>
@endsection

@section('content')
<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title">جميع المناقصات</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (قائمة المناقصات):
                - $tenders قادمة من Admin\TenderController@index.
                - أي مناقصة منشورة تظهر في الواجهة ضمن صفحة المناقصات (SiteController@tenders).
                - طلبات تقديم العروض تأتي من الواجهة وتُحفظ ضمن contacts وتُتابع من قسم الرسائل/الطلبات.
            -->
            <form action="{{ route('admin.tenders.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.tenders.create') }}" class="btn btn-danger btn-sm">
                <i class="fas fa-plus"></i> إضافة مناقصة جديدة
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>العنوان</th>
                    <th>نوع العمل</th>
                    <th>تاريخ الإغلاق</th>
                    <th>الحالة</th>
                    <th style="width: 150px">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية قائمة المناقصات: تعرض سجلات جدول tenders -->
                @forelse($tenders as $tender)
                    <tr>
                        <td>{{ $tender->id }}</td>
                        <td>{{ $tender->title }}</td>
                        <td>{{ $tender->work_type ?: '-' }}</td>
                        <td>{{ optional($tender->closing_date)->format('Y-m-d H:i') }}</td>
                        <td>{{ $tender->status }}</td>
                        <td>
                            <a href="{{ route('admin.tenders.edit', $tender) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.tenders.destroy', $tender) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد مناقصات مضافة حالياً.</td>
                    </tr>
                @endforelse
                <!-- نهاية قائمة المناقصات -->
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() -->
    <div class="card-footer">{{ $tenders->links() }}</div>
</div>
@endsection
