@extends('admin.layouts.app')

@section('title', 'الإشعارات')
@section('page_title', 'إدارة الإشعارات')

@section('breadcrumb')
    <li class="breadcrumb-item active">الإشعارات</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">كل الإشعارات</h3>
        <!--
            خريطة تدفق البيانات (الإشعارات):
            - $notifications قادمة من Admin\NotificationController@index (إشعارات المستخدم الحالي).
            - هذه الإشعارات تُنشأ غالبا عند وقوع أحداث مثل وصول طلب تواصل جديد من الواجهة الأمامية.
            - زر "تعليم الكل" يحدّث read_at ليُعتبر الإشعار مقروءا.
        -->
        <form action="{{ route('admin.notifications.read-all') }}" method="POST">
            @csrf
            @method('PATCH')
            <button class="btn btn-sm btn-primary">تعليم الكل كمقروء</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>النص</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                <!-- بداية قائمة الإشعارات: بياناتها تأتي من جدول notifications (Laravel) -->
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->data['title'] ?? 'إشعار' }}</td>
                        <td>{{ $notification->data['message'] ?? '' }}</td>
                        <td>{{ $notification->read_at ? 'مقروء' : 'غير مقروء' }}</td>
                        <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @if(!$notification->read_at)
                                <!-- تعليم إشعار محدد كمقروء -->
                                <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-success">تعليم كمقروء</button>
                                </form>
                            @endif
                            @if(!empty($notification->data['url']))
                                <!-- رابط اختياري يُوجّه للإجراء المرتبط (مثل فتح رسالة/طلب) -->
                                <a class="btn btn-sm btn-info" href="{{ $notification->data['url'] }}">فتح</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">لا توجد إشعارات.</td></tr>
                @endforelse
                <!-- نهاية قائمة الإشعارات -->
            </tbody>
        </table>
    </div>
    @if(method_exists($notifications, 'links'))
        <!-- ترقيم الصفحات: ناتج paginate() من المتحكم -->
        <div class="card-footer">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
