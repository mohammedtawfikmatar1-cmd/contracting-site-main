@extends('admin.layouts.app')

@section('title', 'إدارة العملاء')
@section('page_title', 'العملاء (شعارات وشركاء)')

@section('breadcrumb')
    <li class="breadcrumb-item active">العملاء</li>
@endsection

@section('content')
<div class="card card-outline card-secondary mb-3">
    <div class="card-body">
        <!--
            خريطة تدفق البيانات:
            - يتحكم هذا المفتاح في إظهار رابط "عملاؤنا" في القائمة الرئيسية وفي مسار /clients.
            - لا يؤثر على شريط الشعارات في الصفحة الرئيسية (يظهر عند وجود عملاء منشورين ومشاريع مرتبطة).
        -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <strong>صفحة عملاؤنا للزوار</strong>
                <div class="text-muted small mt-1">
                    @if($clientsPageEnabled)
                        مفعّل حالياً: يظهر الرابط في الهيدر وتعمل صفحة <code>/clients</code>.
                    @else
                        معطّل حالياً: لا يظهر رابط "عملاؤنا" ولا صفحة العملاء للزوار.
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('admin.clients.page-toggle') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="enabled" value="{{ $clientsPageEnabled ? '0' : '1' }}">
                    <button type="submit" class="btn btn-sm {{ $clientsPageEnabled ? 'btn-warning' : 'btn-success' }}">
                        {{ $clientsPageEnabled ? 'إخفاء صفحة عملاؤنا' : 'إظهار صفحة عملاؤنا' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">قائمة العملاء</h3>
        <div class="card-tools">
            <form action="{{ route('admin.clients.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> إضافة عميل
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>الاسم</th>
                    <th>الشعار</th>
                    <th>المشاريع</th>
                    <th>الحالة</th>
                    <th style="width: 180px">العمليات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->name }}</td>
                        <td>
                            @if($client->logo_url)
                                <img src="{{ $client->logo_url }}" alt="" style="max-height: 36px;">
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge badge-info">{{ $client->projects_count }}</span></td>
                        <td>
                            <span class="badge {{ $client->is_published ? 'badge-success' : 'badge-secondary' }}">
                                {{ $client->is_published ? 'منشور' : 'مخفي' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد حذف العميل؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا يوجد عملاء بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $clients->links() }}</div>
</div>
@endsection
