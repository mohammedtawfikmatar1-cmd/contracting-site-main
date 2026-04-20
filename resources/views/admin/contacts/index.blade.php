@extends('admin.layouts.app')

@section('title', 'الرسائل والطلبات')
@section('page_title', 'الرسائل والطلبات الواردة')

@section('breadcrumb')
    <li class="breadcrumb-item active">الرسائل</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">قائمة الرسائل والطلبات</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (الرسائل والطلبات):
                - $contacts قادمة من Admin\ContactController@index.
                - هذه السجلات تُنشأ غالبا من الواجهة الأمامية عبر ContactRequestController (نماذج التواصل/الخدمة/التوظيف/المناقصات).
                - متابعة الحالة هنا (pending/in_progress/completed) تساعد الإدارة على تنظيم معالجة الطلبات.
            -->
            <form action="{{ route('admin.contacts.index') }}" method="GET" class="d-inline-block">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>الاسم الكامل</th>
                        <th>البريد الإلكتروني</th>
                        <th>رقم الهاتف</th>
                        <th>نوع الطلب</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- بداية قائمة الطلبات: تعرض ما وصل من الموقع (ومنها ملفات CV/عروض) -->
                    @forelse($contacts as $contact)
                        <tr>
                            <td>{{ $contact->full_name }}</td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>{{ $contact->request_type_label }}</td>
                            <td>{{ $contact->status }}</td>
                            <td>{{ $contact->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-sm btn-primary">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">لا توجد رسائل جديدة حالياً.</td>
                        </tr>
                    @endforelse
                    <!-- نهاية قائمة الطلبات -->
                </tbody>
            </table>
        </div>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
    <div class="card-footer">{{ $contacts->links() }}</div>
</div>
@endsection
