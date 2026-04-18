<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>تسجيل الدخول | لوحة التحكم</title>

  <link rel="stylesheet" href="{{ asset('public/admin/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css') }}">
</head>
<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <b>لوحة التحكم</b>
    </div>
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">تسجيل الدخول للمتابعة</p>

        <!--
          خريطة تدفق البيانات (تسجيل الدخول):
          - النموذج يرسل إلى route('admin.login.store') => Admin\AuthController@store.
          - عند نجاح المصادقة يتم إنشاء جلسة للمستخدم ثم إعادة توجيهه للوحة القيادة.
        -->
        @if ($errors->any())
          <div class="alert alert-danger">
            {{ $errors->first() }}
          </div>
        @endif

        <form action="{{ route('admin.login.store') }}" method="POST">
          @csrf
          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required autofocus>
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

          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">تذكرني</label>
              </div>
            </div>
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block">دخول</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="{{ asset('public/admin/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('public/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('public/admin/dist/js/adminlte.min.js') }}"></script>
</body>
</html>

