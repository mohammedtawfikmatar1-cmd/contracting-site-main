@extends('admin.layouts.app')

@section('title', 'تعديل مستخدم')
@section('page_title', 'تعديل مستخدم')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">المستخدمون</a></li>
  <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
  <div class="card card-info">
    <div class="card-header"><h3 class="card-title">بيانات المستخدم</h3></div>

    <!--
      خريطة تدفق البيانات (تعديل مستخدم):
      - التحديث يتم عبر Admin\UserController@update.
      - كلمة المرور اختيارية: إذا تُركت فارغة فلن يتم تغييرها (حسب منطق المتحكم).
      - تغيير is_super_admin يؤثر على صلاحيات الإدارة، ولا ينعكس مباشرة في الواجهة الأمامية لكنه يحدد من يملك التحكم.
    -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card-body">
        <div class="form-group">
          <label>الاسم</label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
          <label>البريد الإلكتروني</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
          <label>كلمة المرور الجديدة (اختياري)</label>
          <input type="password" name="password" class="form-control" autocomplete="new-password">
          <small class="text-muted">اتركها فارغة إذا لا تريد تغيير كلمة المرور.</small>
        </div>

        <div class="form-group">
          <label>تأكيد كلمة المرور الجديدة</label>
          <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
        </div>

        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_super_admin" name="is_super_admin" value="1" {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_super_admin">مدير عام</label>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <button class="btn btn-info">حفظ</button>
        <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">رجوع</a>
      </div>
    </form>
  </div>
@endsection

