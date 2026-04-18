<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <!--
    الغرض من الملف:
    القالب العام للوحة التحكم (Admin Layout) الذي يحتوي على شريط علوي + قائمة جانبية
    ويحقن محتوى الصفحات الإدارية عبر أقسام Blade المختلفة.

    خريطة تدفق البيانات:
    - auth()->user(): هو المستخدم الحالي المسجل دخوله من نظام الإدارة.
    - $adminUnreadNotificationsCount و $adminLatestNotifications:
      تُجهز عادة عبر مزود بيانات/مشاركة View (View Composer) لتعكس إشعارات النظام
      الناتجة عن أحداث مثل وصول طلبات التواصل من الواجهة الأمامية.
    - روابط القائمة الجانبية: تقود إلى أقسام إدارة المحتوى التي تنعكس في الواجهة الأمامية
      (خدمات/مشاريع/أخبار/صفحات/مناقصات/وظائف) وإعدادات الهوية البصرية.
  -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>@yield('title', 'لوحة التحكم') | شركة المقاولات</title>
  
  <link rel="stylesheet" href="{{ asset('public/admin/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/fonts/SansPro/SansPro.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin/css/mycustomstyle.css') }}">
  @yield('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav align-items-center">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item ml-2">
        <!-- نموذج البحث داخل لوحة التحكم (AdminSearchController) -->
        <form action="{{ route('admin.search') }}" method="GET" class="form-inline">
          <div class="input-group input-group-sm">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-navbar" placeholder="بحث في لوحة التحكم">
            <div class="input-group-append">
              <button class="btn btn-navbar" type="submit"><i class="fas fa-search"></i></button>
            </div>
          </div>
        </form>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item d-flex align-items-center mr-2">
        <span class="text-muted small">{{ auth()->user()?->name }}</span>
      </li>

      <li class="nav-item">
        <!-- تسجيل الخروج: يمر عبر AdminAuthController@destroy -->
        <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
          @csrf
          <button class="btn btn-sm btn-outline-secondary" type="submit" title="تسجيل خروج">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </form>
      </li>

      <li class="nav-item dropdown">
        <!-- قائمة الإشعارات: تعكس إشعارات لوحة التحكم (مثال: طلب تواصل جديد) -->
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          @if($adminUnreadNotificationsCount > 0)
            <span class="badge badge-danger navbar-badge">{{ $adminUnreadNotificationsCount }}</span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-left">
          <span class="dropdown-item dropdown-header">{{ $adminUnreadNotificationsCount }} إشعارات غير مقروءة</span>
          <div class="dropdown-divider"></div>
          @forelse($adminLatestNotifications as $notification)
            <a href="{{ $notification->data['url'] ?? route('admin.notifications.index') }}" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> {{ $notification->data['message'] ?? 'إشعار جديد' }}
              <span class="float-left text-muted text-sm">{{ $notification->created_at->diffForHumans() }}</span>
            </a>
            <div class="dropdown-divider"></div>
          @empty
            <span class="dropdown-item text-center text-muted">لا توجد إشعارات</span>
            <div class="dropdown-divider"></div>
          @endforelse
          <a href="{{ route('admin.notifications.index') }}" class="dropdown-item dropdown-footer">عرض كل الإشعارات</a>
        </div>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
      <img src="{{ asset('public/admin/dist/img/AdminLTELogo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">لوحة التحكم</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>الرئيسية</p>
            </a>
          </li>

          <li class="nav-header">إدارة المحتوى</li>
          <!--
            أقسام المحتوى التالية هي المصدر الرئيسي لبيانات الموقع الأمامي:
            أي تعديل هنا (مع النشر/التفعيل) ينعكس مباشرة في صفحات الواجهة.
          -->

          <li class="nav-item">
            <a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-copy"></i>
              <p>الصفحات</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-concierge-bell"></i>
              <p>الخدمات</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.projects.index') }}" class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-project-diagram"></i>
              <p>المشاريع</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-newspaper"></i>
              <p>الأخبار</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.tenders.index') }}" class="nav-link {{ request()->routeIs('admin.tenders.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-contract"></i>
              <p>المناقصات</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-briefcase"></i>
              <p>الوظائف</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-handshake"></i>
              <p>العملاء</p>
            </a>
          </li>

          <li class="nav-header">التواصل والإعدادات</li>

          <li class="nav-item">
            <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-envelope"></i>
              <p>الرسائل والطلبات</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-cogs"></i>
              <p>إعدادات الموقع</p>
            </a>
          </li>

        @can('manage-users')
          <!-- إدارة المستخدمين: تحدد من يستطيع إدارة محتوى الموقع -->
          <li class="nav-item">
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>المستخدمون</p>
            </a>
          </li>
        @endcan

        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">@yield('page_title')</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
              @yield('breadcrumb')
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <!-- رسالة نجاح عامة بعد عمليات الحفظ/التحديث في لوحة التحكم -->
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
      </div>
    </div>
  </div>

<footer class="main-footer" style="display: block; visibility: visible; clear: both; background: #fff; border-top: 1px solid #dee2e6; padding: 1rem; color: #869099;">
    
    <div class="float-left d-none d-sm-inline">
        <i class="fas fa-chalkboard-teacher text-info"></i>
        <strong style="color: #333;">إشراف:</strong> 
        <span class="badge badge-info shadow-sm" style="font-size: 0.9rem;">أ. هدى الشيخ</span>
    </div>

    <div class="text-right">
        <i class="fas fa-users-cog text-primary"></i>
        <strong style="color: #333;">فريق العمل والإعداد:</strong>
        <div class="mt-2 d-inline-block">
            <span class="badge badge-light border shadow-sm mx-1">محمد مطر</span>
            <span class="badge badge-light border shadow-sm mx-1">قصي</span>
            <span class="badge badge-light border shadow-sm mx-1">أسامة</span>
            <span class="badge badge-light border shadow-sm mx-1">محمد عبد الواحد</span>
            <span class="badge badge-light border shadow-sm mx-1">عمر المزجاجي</span>
            <span class="badge badge-light border shadow-sm mx-1">عبد الصمد</span>
        </div>
    </div>
</footer>
</div>

@include('admin.partials.icon-picker-modal')

<script src="{{ asset('public/admin/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('public/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('public/admin/dist/js/adminlte.min.js') }}"></script>
<script>
  /**
   * اختيار أيقونة الخدمة: نافذة منبثقة متوافقة مع Bootstrap 4 وFont Awesome المضمّن في لوحة التحكم.
   * يستبدل fontawesome-iconpicker القديم الذي كان يتعارض مع هيكل input-group واتجاه RTL.
   */
  (function ($) {
    var $modal = $('#adminIconPickerModal');
    var $activeInput = null;

    function syncPreview($input) {
      var val = ($input.val() || '').trim();
      var $wrap = $input.closest('.icon-picker-wrap');
      var $icon = $wrap.find('.icon-picker-preview i');
      if ($icon.length) {
        $icon.attr('class', val || 'fas fa-icons');
      }
    }

    var $iconSearch = $('#adminIconPickerSearch');

    function adminFilterIconGrid(query) {
      var q = (query || '').toLowerCase().trim();
      $('#adminIconPickerGrid .admin-icon-grid-cell').each(function () {
        var hay = ($(this).attr('data-icon-filter') || '');
        $(this).toggle(q === '' || hay.indexOf(q) !== -1);
      });
    }

    $modal.on('shown.bs.modal', function () {
      if ($iconSearch.length) {
        $iconSearch.val('');
      }
      adminFilterIconGrid('');
    });

    $iconSearch.on('input', function () {
      adminFilterIconGrid($(this).val());
    });

    $(document).on('click', '.btn-icon-picker-open', function (e) {
      e.preventDefault();
      $activeInput = $(this).closest('.icon-picker-wrap').find('.icon-picker');
      if ($modal.length) {
        $modal.modal('show');
      }
    });

    $modal.on('click', '.admin-icon-pick-btn', function () {
      var cls = $(this).data('icon-class');
      if ($activeInput && $activeInput.length && cls) {
        $activeInput.val(cls);
        syncPreview($activeInput);
      }
      $modal.modal('hide');
    });

    $(document).on('input change', '.icon-picker', function () {
      syncPreview($(this));
    });

    $('.icon-picker').each(function () {
      syncPreview($(this));
    });
  })(jQuery);
</script>
@yield('scripts')
</body>
</html>
