<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>تهيئة أول حساب | لوحة التحكم</title>

  <link rel="stylesheet" href="{{ asset('public/admin/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css') }}">
</head>
<body class="hold-transition login-page">
  <div class="login-box" style="width: min(520px, 92vw);">
    <div class="login-logo">
      <b>تهيئة لوحة التحكم</b>
    </div>
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">إنشاء حساب المدير الأول</p>

        <!--
          خريطة تدفق البيانات (الإعداد الأول):
          - هذا النموذج يظهر فقط إذا لم يوجد أي مستخدم في قاعدة البيانات.
          - يرسل إلى route('admin.setup.store') => Admin\AuthController@setupStore.
          - يتم إنشاء أول مستخدم كـ is_super_admin ثم تسجيل دخوله مباشرة.
        -->
        @if ($errors->any())
          <div class="alert alert-danger">
            {{ $errors->first() }}
          </div>
        @endif

        <form action="{{ route('admin.setup.store') }}" method="POST">
          @csrf

          <div class="input-group mb-3">
            <input type="text" name="name" class="form-control" placeholder="الاسم الكامل" value="{{ old('name') }}" required autofocus>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>

          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>

          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>

          <div class="input-group mb-3">
            <input type="password" name="password_confirmation" class="form-control" placeholder="تأكيد كلمة المرور" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-check"></span>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-success btn-block">إنشاء الحساب والدخول</button>
        </form>
      </div>
    </div>
  </div>

  <script src="{{ asset('public/admin/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('public/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('public/admin/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
