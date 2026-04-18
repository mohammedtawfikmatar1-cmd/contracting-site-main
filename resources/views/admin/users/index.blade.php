@extends('admin.layouts.app')

@section('title', 'المستخدمون')
@section('page_title', 'إدارة المستخدمين')

@section('breadcrumb')
  <li class="breadcrumb-item active">المستخدمون</li>
@endsection

@section('content')
  <div class="card card-outline card-info">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title mb-0">جميع المستخدمين</h3>
      <a class="btn btn-sm btn-info" href="{{ route('admin.users.create') }}">
        <i class="fas fa-plus"></i> إضافة مستخدم
      </a>
    </div>
    <div class="card-body p-0">
      <!--
        خريطة تدفق البيانات (المستخدمون):
        - $users قادمة من Admin\UserController@index.
        - هذه الشاشة تتحكم في من يملك صلاحية الدخول للإدارة وإدارة المحتوى الذي ينعكس على الواجهة الأمامية.
        - is_super_admin يحدد صلاحيات عليا داخل لوحة التحكم.
      -->
      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>البريد</th>
            <th>مدير عام</th>
            <th>عمليات</th>
          </tr>
        </thead>
        <tbody>
          <!-- بداية قائمة المستخدمين -->
          @forelse($users as $user)
            <tr>
              <td>{{ $user->id }}</td>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                @if($user->is_super_admin)
                  <span class="badge badge-success">نعم</span>
                @else
                  <span class="badge badge-secondary">لا</span>
                @endif
              </td>
              <td style="white-space:nowrap;">
                <a class="btn btn-sm btn-primary" href="{{ route('admin.users.edit', $user) }}">
                  تعديل
                </a>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted">لا يوجد مستخدمون.</td>
            </tr>
          @endforelse
          <!-- نهاية قائمة المستخدمين -->
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      <!-- ترقيم الصفحات: ناتج paginate() -->
      {{ $users->links() }}
    </div>
  </div>
@endsection

