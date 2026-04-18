@extends('admin.layouts.app')

@section('title', 'إدارة الأخبار')
@section('page_title', 'قائمة الأخبار')

@section('breadcrumb')
    <li class="breadcrumb-item active">الأخبار</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">جميع الأخبار</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (قائمة الأخبار):
                - $news قادمة من Admin\NewsController@index.
                - حالة النشر وتاريخ النشر يتحكمان في ظهور الخبر بالواجهة الأمامية (صفحة الأخبار + التفاصيل).
            -->
            <form action="{{ route('admin.news.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> إضافة خبر جديد
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>العنوان</th>
                    <th>التصنيف</th>
                    <th>الحالة</th>
                    <th>تاريخ النشر</th>
                    <th style="width: 150px">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية قائمة الأخبار: تعرض سجلات جدول news كما أدخلتها الإدارة -->
                @forelse($news as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->category ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $item->is_published ? 'badge-success' : 'badge-secondary' }}">
                                {{ $item->is_published ? 'منشور' : 'مسودة' }}
                            </span>
                        </td>
                        <td>{{ optional($item->published_at)->format('Y-m-d H:i') ?: '-' }}</td>
                        <td>
                            <!-- تعديل/حذف الخبر: ينعكس على الواجهة الأمامية وفقا لحالة النشر وتاريخ النشر -->
                            <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.news.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد بيانات لعرضها حالياً.</td>
                    </tr>
                @endforelse
                <!-- نهاية قائمة الأخبار -->
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
    <div class="card-footer">{{ $news->links() }}</div>
</div>
@endsection
