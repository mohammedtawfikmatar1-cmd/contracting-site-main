{{--
  نافذة اختيار أيقونة Font Awesome (solid: fas) — القائمة تُبنى من ملف fontawesome.css المرفق مع لوحة التحكم
  لذلك تغطي كل الأيقونات المتاحة في الحزمة وليست قائمة يدوية محدودة.
  حقل البحث يصفّي الأزرار حسب الاسم دون إعادة تحميل الصفحة.
--}}
@php
    $faCssPath = public_path('admin/plugins/fontawesome-free/css/fontawesome.css');
    $cacheKey = 'admin_fa_solid_icon_list_' . (int) @filemtime($faCssPath);

    $adminFaIconChoices = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($faCssPath) {
        if (! is_readable($faCssPath)) {
            return [
                'fas fa-building', 'fas fa-city', 'fas fa-home', 'fas fa-tools', 'fas fa-wrench',
                'fas fa-hard-hat', 'fas fa-truck', 'fas fa-users', 'fas fa-icons',
            ];
        }

        $css = file_get_contents($faCssPath);
        preg_match_all('/\.fa-([a-z0-9-]+):before\b/', $css, $m);
        $names = array_values(array_unique($m[1]));

        // استبعاد أصناف المساعدة (أحجام، محاذاة، قوائم…) وليست أيقونات معروضة.
        $excludeExact = [
            'lg', 'xs', 'sm', '1x', '2x', '3x', '4x', '5x', '6x', '7x', '8x', '9x', '10x',
            'fw', 'ul', 'ol', 'li', 'border', 'pull-left', 'pull-right', 'spin', 'pulse',
            'rotate-90', 'rotate-180', 'rotate-270', 'flip-horizontal', 'flip-vertical',
            'stack', 'stack-1x', 'stack-2x', 'inverse', 'sr-only', 'sr-only-focusable',
            'align-left', 'align-center', 'align-right', 'align-justify',
        ];

        $out = [];
        foreach ($names as $n) {
            if (in_array($n, $excludeExact, true)) {
                continue;
            }
            if (preg_match('/^rotate-|^flip-/', $n)) {
                continue;
            }
            $out[] = 'fas fa-' . $n;
        }
        sort($out);

        return $out;
    });
@endphp
<div class="modal fade" id="adminIconPickerModal" tabindex="-1" role="dialog" aria-labelledby="adminIconPickerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 960px;" role="document">
        <div class="modal-content">
            <div class="modal-header align-items-center flex-wrap">
                <h5 class="modal-title mb-0" id="adminIconPickerModalTitle">اختيار أيقونة</h5>
                <div class="ml-auto d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0" style="gap:8px;">
                    <span class="text-muted small d-none d-sm-inline">{{ count($adminFaIconChoices) }} أيقونة</span>
                    <input type="search" id="adminIconPickerSearch" class="form-control form-control-sm" style="min-width:200px;max-width:280px;" placeholder="بحث (مثال: truck, user, home)" autocomplete="off">
                </div>
                <button type="button" class="close mr-2" data-dismiss="modal" aria-label="إغلاق">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-2">
                    تُستخرج الأسماء من حزمة Font Awesome المثبتة مع لوحة التحكم؛ يمكنك أيضاً كتابة الصنف يدوياً في الحقل (مثل <code>fas fa-helmet-safety</code> إن وُجد في إصدار أحدث من الموقع الأمامي).
                </p>
                <div class="row text-center" id="adminIconPickerGrid">
                    @foreach($adminFaIconChoices as $iconClass)
                        <div class="col-4 col-sm-3 col-md-2 col-lg-1 mb-2 admin-icon-grid-cell" data-icon-filter="{{ strtolower($iconClass) }}">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm btn-block py-2 admin-icon-pick-btn"
                                    data-icon-class="{{ $iconClass }}"
                                    title="{{ $iconClass }}">
                                <i class="{{ $iconClass }}"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
