@extends('site.layouts.app')

@section('title', 'تسجيل الدخول - شركة مقاولات')

@section('styles')
@vite(['resources/css/login.css'])
    {{-- <link rel="stylesheet" href="{{ asset('css/login.css') }}" /> --}}
@endsection

@section('content')
    <section class="login-section" id="login">
      <div class="login-card">
        <div class="logo-icon" aria-hidden="true">
          <svg width="60" height="60" viewBox="0 0 40 40" fill="none">
            <rect width="40" height="40" rx="12" fill="url(#hg)"/>
            <path d="M12 30V16l8-5 8 5v14" stroke="white" stroke-width="2.2" stroke-linejoin="round" fill="none"/>
            <path d="M16 30v-9h8v9" stroke="white" stroke-width="2.2" stroke-linejoin="round" fill="none"/>
            <defs>
              <linearGradient id="hg" x1="0" y1="0" x2="40" y2="40">
                <stop stop-color="#ff7a1a"/>
                <stop offset="1" stop-color="#b34500"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
        <h2>تسجيل الدخول</h2>
        <div class="login-error" id="loginError">اسم المستخدم أو كلمة المرور غير صحيحة</div>
        <!--
          ملاحظة تقنية مهمة:
          هذه الصفحة "تسجيل دخول" هي نموذج تجريبي (Front-end demo) ولا تتصل فعليا بمصادقة Laravel.
          مسار الدخول الحقيقي للإدارة هو: route('admin.login') ويُدار عبر Admin\AuthController.
          في routes/web.php تم توجيه /login إلى صفحة دخول الإدارة.
        -->
        <form id="loginForm" autocomplete="off">
          @csrf
          <div>
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required autocomplete="username" />
          </div>
          <div>
            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password" />
          </div>
          <button type="submit" class="btn-login">دخول</button>
        </form>
        <div class="login-note">
          <i class="fas fa-lock"></i> بياناتك سرية وآمنة
        </div>
      </div>
    </section>
@endsection

@section('scripts')
<script>
    // Simple login validation (demo only)
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();
      var user = document.getElementById('username').value.trim();
      var pass = document.getElementById('password').value.trim();
      var err  = document.getElementById('loginError');
      // Demo: username=admin, password=1234
      if(user === 'admin' && pass === '1234') {
        err.style.display = 'none';
        window.location.href = "{{ route('home') }}";
      } else {
        err.style.display = 'block';
      }
    });
</script>
@endsection
