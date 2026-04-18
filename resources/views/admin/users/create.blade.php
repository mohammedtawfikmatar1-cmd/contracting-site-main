@extends('admin.layouts.app')

@section('title', 'إضافة مستخدم')
@section('page_title', 'إضافة مستخدم')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">المستخدمون</a></li>
  <li class="breadcrumb-item active">إضافة</li>
@endsection

@section('content')
  <div class="card card-info">
    <div class="card-header"><h3 class="card-title">بيانات المستخدم</h3></div>

    <!--
      خريطة تدفق البيانات (إضافة مستخدم):
      - الحفظ يتم عبر Admin\UserController@store.
      - هذا المستخدم سيستطيع الدخول إلى لوحة التحكم وإدارة المحتوى الذي ينعكس على واجهة الموقع.
      - تفعيل "مدير عام" يمنحه صلاحيات عليا (حسب سياسات المشروع/الـ Gates).
    -->
    <form action="{{ route('admin.users.store') }}" method="POST">
      @csrf
      <div class="card-body">
        <div class="form-group">
          <label>الاسم</label>
          <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
          <label>البريد الإلكتروني</label>
          <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
          <label>كلمة المرور</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
          <label>تأكيد كلمة المرور</label>
          <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_super_admin" name="is_super_admin" value="1" {{ old('is_super_admin') ? 'checked' : '' }}>
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

