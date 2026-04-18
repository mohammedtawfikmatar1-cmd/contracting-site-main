@extends('admin.layouts.app')

@section('title', 'إدارة الصفحات')
@section('page_title', 'قائمة الصفحات')

@section('breadcrumb')
    <li class="breadcrumb-item active">الصفحات</li>
@endsection

@section('content')
<div class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title">جميع الصفحات</h3>
        <div class="card-tools">
            <!--
                خريطة تدفق البيانات (الصفحات):
                - $pages قادمة من Admin\PageController@index.
                - كل صفحة منشورة (is_published) تظهر في الواجهة عبر:
                  - route('pages.show') => SiteController@page
                  - وكذلك عبر المسار الديناميكي catch-all في routes/web.php عند الحاجة.
                - اختيار template يحدد أي Blade سيتم استخدامه إن وجد ضمن resources/views/site/pages/*.
            -->
            <form action="{{ route('admin.pages.index') }}" method="GET" class="d-inline-block mr-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="بحث...">
                    <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-plus"></i> إضافة صفحة جديدة
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>العنوان</th>
                    <th>الرابط (Slug)</th>
                    <th>القالب</th>
                    <th>الحالة</th>
                    <th style="width: 150px">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية قائمة الصفحات: تعرض سجلات جدول pages -->
                @forelse($pages as $page)
                    <tr>
                        <td>{{ $page->id }}</td>
                        <td>{{ $page->title }}</td>
                        <td>{{ $page->slug }}</td>
                        <td>{{ $page->template }}</td>
                        <td>
                            <span class="badge {{ $page->is_published ? 'badge-success' : 'badge-secondary' }}">
                                {{ $page->is_published ? 'منشورة' : 'مسودة' }}
                            </span>
                        </td>
                        <td>
                            <!-- تعديل/حذف الصفحة: ينعكس في الواجهة حسب حالة النشر -->
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-primary">تعديل</a>
                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد صفحات مضافة حالياً.</td>
                    </tr>
                @endforelse
                <!-- نهاية قائمة الصفحات -->
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات: ناتج paginate() -->
    <div class="card-footer">{{ $pages->links() }}</div>
</div>
@endsection
