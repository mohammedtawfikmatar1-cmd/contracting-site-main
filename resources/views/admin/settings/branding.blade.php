@extends('admin.layouts.app')

@section('title', 'الهوية البصرية ومعلومات الشركة')
@section('page_title', 'الهوية البصرية ومعلومات الشركة')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">إعدادات الموقع</a></li>
  <li class="breadcrumb-item active">الهوية البصرية</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title">تخصيص ألوان الموقع ومعلومات الشركة</h3>
        </div>

        <!--
          خريطة تدفق البيانات (الهوية البصرية):
          - $settings هنا عبارة عن خريطة key => value قادمة من Admin\SettingController@branding.
          - حفظ هذا النموذج يتم عبر Admin\SettingController@saveBranding والذي يستخدم Setting::setValue.
          - الألوان تنعكس في الواجهة ضمن site/layouts/app عبر متغيرات CSS.
          - الشعارات والفافيكون والصور تنعكس في header/footer والصفحة الرئيسية ومن نحن.
          - site_menu ينعكس في روابط التنقل الرئيسية؛ الصفحات المنشورة من «إدارة المحتوى ← الصفحات» تظهر في الواجهة ضمن مجموعة «صفحات إضافية» دون إعداد إضافي هنا.
          - حقول home_hero_* تُعرض في بانر الصفحة الرئيسية (index).
        -->
        <form action="{{ route('admin.settings.branding.save') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="card-body">
            @php
                $currentPreset = old('theme_preset', $settings['theme_preset'] ?? 'custom');
            @endphp
            <!--
              اختيار لوحة الألوان:
              - theme_preset يُحفظ في settings؛ عند اختيار لوحة جاهزة يُعاد ضبط الألوان الثلاثة تلقائياً في المتحكم.
              - عند "تخصيص يدوي" تُؤخذ القيم من حقول اللون الثلاثة.
            -->
            <div class="form-group">
              <label class="d-block font-weight-bold mb-2">لوحة الألوان</label>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="w-100 border rounded p-3 h-100 mb-0 theme-preset-option" style="cursor:pointer;">
                    <div class="d-flex align-items-start">
                      <input type="radio" name="theme_preset" value="custom" class="mt-1 ml-2" {{ $currentPreset === 'custom' ? 'checked' : '' }}>
                      <div>
                        <strong>تخصيص يدوي</strong>
                        <div class="text-muted small mt-1">استخدم حقول الألوان أدناه لاختيار القيم بنفسك.</div>
                      </div>
                    </div>
                  </label>
                </div>
                @foreach($themePresets as $presetKey => $preset)
                  <div class="col-md-4 mb-3">
                    <label class="w-100 border rounded p-3 h-100 mb-0 theme-preset-option" style="cursor:pointer;"
                           data-preset-primary="{{ $preset['primary'] }}"
                           data-preset-secondary="{{ $preset['secondary'] }}"
                           data-preset-accent="{{ $preset['accent'] }}">
                      <div class="d-flex align-items-start">
                        <input type="radio" name="theme_preset" value="{{ $presetKey }}" class="mt-1 ml-2" {{ $currentPreset === $presetKey ? 'checked' : '' }}>
                        <div class="flex-grow-1">
                          <div class="rounded mb-2" style="height:10px;background:linear-gradient(90deg,{{ $preset['primary'] }},{{ $preset['secondary'] }},{{ $preset['accent'] }});"></div>
                          <strong class="small d-block">{{ $preset['label'] }}</strong>
                        </div>
                      </div>
                    </label>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="row" id="theme-custom-colors">
              <div class="col-md-4">
                <div class="form-group">
                  <label>اللون الرئيسي</label>
                  <div class="input-group">
                    <input type="color" id="theme_primary_color" name="theme_primary_color" class="form-control theme-color-input" value="{{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#ff7a1a') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#theme_primary_color" value="{{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#ff7a1a') }}" dir="ltr">
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>اللون الثانوي</label>
                  <div class="input-group">
                    <input type="color" id="theme_secondary_color" name="theme_secondary_color" class="form-control theme-color-input" value="{{ old('theme_secondary_color', $settings['theme_secondary_color'] ?? '#0b5ed7') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#theme_secondary_color" value="{{ old('theme_secondary_color', $settings['theme_secondary_color'] ?? '#0b5ed7') }}" dir="ltr">
                  </div>
                </div>
              </div>
              <div  class="col-md-4">
                <div class="form-group">
                  <label>لون الإبراز</label>
                  <div class="input-group">
                    <input type="color" id="theme_accent_color" name="theme_accent_color" class="form-control theme-color-input" value="{{ old('theme_accent_color', $settings['theme_accent_color'] ?? '#22c55e') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#theme_accent_color" value="{{ old('theme_accent_color', $settings['theme_accent_color'] ?? '#22c55e') }}" dir="ltr">
                  </div>
                </div>
              </div>
            </div>
            <p class="text-muted small">
              ملاحظة: اللون الرئيسي يتحكم بمعظم عناصر التمييز في الموقع (أزرار، عناوين فرعية، تدرجات مرتبطة بـ CSS). بعد الحفظ، حدّث الصفحة الأمامية لرؤية التغيير.
            </p>

            <hr>

            <h5 class="mb-3">ألوان الهيكل (الخلفية / الهيدر / الفوتر)</h5>
            <p class="text-muted small mb-3">
              هذا القسم للتحكم بالألوان العامة التي تحدد شكل الموقع: خلفية الصفحة، وخلفية الهيدر، وخلفية الفوتر.
            </p>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>خلفية الموقع</label>
                  <div class="input-group">
                    <input type="color" id="body_bg_color" name="body_bg_color" class="form-control theme-color-input" value="{{ old('body_bg_color', $settings['body_bg_color'] ?? '#0d0f14') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#body_bg_color" value="{{ old('body_bg_color', $settings['body_bg_color'] ?? '#0d0f14') }}" dir="ltr">
                  </div>
                  <small class="text-muted d-block mt-1">تؤثر على خلفية الموقع بالكامل.</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>خلفية الفوتر</label>
                  <div class="input-group">
                    <input type="color" id="footer_bg_color" name="footer_bg_color" class="form-control theme-color-input" value="{{ old('footer_bg_color', $settings['footer_bg_color'] ?? '#07080b') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#footer_bg_color" value="{{ old('footer_bg_color', $settings['footer_bg_color'] ?? '#07080b') }}" dir="ltr">
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>خلفية الهيدر</label>
                  <div class="input-group">
                    <input type="color" id="header_bg_color" name="header_bg_color" class="form-control theme-color-input" value="{{ old('header_bg_color', $settings['header_bg_color'] ?? '#0d0f14') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#header_bg_color" value="{{ old('header_bg_color', $settings['header_bg_color'] ?? '#0d0f14') }}" dir="ltr">
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>خلفية الهيدر بعد التمرير</label>
                  <div class="input-group">
                    <input type="color" id="header_scrolled_bg_color" name="header_scrolled_bg_color" class="form-control theme-color-input" value="{{ old('header_scrolled_bg_color', $settings['header_scrolled_bg_color'] ?? '#0d0f14') }}">
                    <input type="text" class="form-control js-color-hex" data-color-input="#header_scrolled_bg_color" value="{{ old('header_scrolled_bg_color', $settings['header_scrolled_bg_color'] ?? '#0d0f14') }}" dir="ltr">
                  </div>
                </div>
              </div>
            </div>

            <!-- <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="enable_multilingual" name="enable_multilingual" value="1" {{ old('enable_multilingual', $settings['enable_multilingual'] ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="enable_multilingual">تفعيل دعم اللغتين (عربي/إنجليزي)</label>
              </div>
              <small class="text-muted d-block mt-1">عند الإيقاف ستختفي حقول اللغة الإنجليزية من لوحة التحكم ويختفي زر تبديل اللغة من الواجهة.</small>
            </div> -->

            <hr>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>اسم الشركة</label>
                  <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings['company_name'] ?? '') }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>البريد الإلكتروني</label>
                  <input type="text" name="company_email" class="form-control" value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>رقم التواصل 1</label>
                  <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>رقم التواصل 2</label>
                  <input type="text" name="company_phone_2" class="form-control" value="{{ old('company_phone_2', $settings['company_phone_2'] ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>العنوان</label>
                  <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $settings['company_address'] ?? '') }}">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>وصف مختصر للفوتر</label>
              <textarea name="footer_brief" class="form-control" rows="4">{{ old('footer_brief', $settings['footer_brief'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
              <label>قائمة الموقع الديناميكية</label>
              <!--
                ملاحظة مهمة:
                واجهة builder بالأسفل تولد JSON وتضعه داخل textarea المخفي site_menu.
                هذا JSON يُحفظ في settings ثم يُحوّل في الواجهة إلى $siteMenu لبناء روابط التنقل.
              -->
              <div id="menu-builder" class="border rounded p-3">
                <div id="menu-items"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-menu-item">
                  <i class="fas fa-plus"></i> إضافة عنصر قائمة
                </button>
              </div>
              <textarea name="site_menu" id="site_menu" class="form-control d-none" rows="6" dir="ltr" placeholder='[{"label":"الرئيسية","url":"https://example.com/"},{"label":"من نحن","url":"https://example.com/about"}]'>{{ old('site_menu', is_array($settings['site_menu'] ?? null) ? json_encode($settings['site_menu'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
              <small class="text-muted d-block mt-1">
                اترك القائمة فارغة لاستخدام القائمة الافتراضية. أما الصفحات الإضافية فتُنشأ من «إدارة المحتوى ← الصفحات» وتُعرض في الهيدر ضمن مجموعة منفصلة تلقائيًا.
              </small>
            </div>

            <hr>

            <!--
              نصوص قسم الهيرو في الصفحة الرئيسية:
              تُحفظ في settings وتُقرأ في resources/views/site/index.blade.php عبر $siteSettings.
            -->
            <h5 class="mb-3">نصوص البانر (الهيرو) في الصفحة الرئيسية</h5>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>عنوان رئيسي توضيحي</label>
                  <input type="text" name="home_hero_title" class="form-control" maxlength="500"
                         placeholder="مثال: شريكك في التشييد والبنية التحتية"
                         value="{{ old('home_hero_title', $settings['home_hero_title'] ?? '') }}">
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>بيان توضيحي (شارة صغيرة فوق العنوان)</label>
                  <input type="text" name="home_hero_badge" class="form-control" maxlength="500"
                         placeholder="مثال: مقاولات عامة — جودة وتسليم في الوقت"
                         value="{{ old('home_hero_badge', $settings['home_hero_badge'] ?? '') }}">
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>وصف توضيحي (سطران تحت العنوان)</label>
                  <textarea name="home_hero_description" class="form-control" rows="3" maxlength="2000"
                            placeholder="عرّف بخدمات شركتك ومجالات عملها بجملة أو جملتين.">{{ old('home_hero_description', $settings['home_hero_description'] ?? '') }}</textarea>
                </div>
              </div>
            </div>

            <hr>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>الشعار الأساسي</label>
                  <!-- رفع صورة: يتم تخزينها في storage ثم حفظ مسارها ضمن settings -->
                  <input type="file" name="logo_main" class="form-control">
                  @if(!empty($settings['logo_main']))
                    <div class="mt-2">
                      <img src="{{ $settings['logo_main'] }}" alt="logo" style="max-height:60px;">
                    </div>
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>الشعار الشفاف</label>
                  <input type="file" name="logo_transparent" class="form-control">
                  @if(!empty($settings['logo_transparent']))
                    <div class="mt-2">
                      <img src="{{ $settings['logo_transparent'] }}" alt="logo" style="max-height:60px;">
                    </div>
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>الأيقونة Favicon</label>
                  <!-- تنعكس مباشرة في <link rel="icon"> داخل site/layouts/app -->
                  <input type="file" name="favicon" class="form-control">
                  @if(!empty($settings['favicon']))
                    <div class="mt-2">
                      <img src="{{ $settings['favicon'] }}" alt="favicon" style="max-height:32px;">
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>صورة هيرو الرئيسية</label>
                  <input type="file" name="home_hero_image" class="form-control">
                  @if(!empty($settings['home_hero_image']))
                    <div class="mt-2">
                      <img src="{{ $settings['home_hero_image'] }}" alt="home hero" style="max-height:80px;">
                    </div>
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>صورة صفحة من نحن</label>
                  <input type="file" name="about_main_image" class="form-control">
                  @if(!empty($settings['about_main_image']))
                    <div class="mt-2">
                      <img src="{{ $settings['about_main_image'] }}" alt="about" style="max-height:80px;">
                    </div>
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>صورة الفوتر</label>
                  <input type="file" name="footer_image" class="form-control">
                  @if(!empty($settings['footer_image']))
                    <div class="mt-2">
                      <img src="{{ $settings['footer_image'] }}" alt="footer" style="max-height:80px;">
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <hr>

            <h5 class="mb-3">روابط منصات التواصل</h5>
            <div class="row">
              <div class="col-md-4"><div class="form-group"><label>فيسبوك</label><input type="text" name="social_facebook" class="form-control" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}"></div></div>
              <div class="col-md-4"><div class="form-group"><label>إكس (X)</label><input type="text" name="social_x" class="form-control" value="{{ old('social_x', $settings['social_x'] ?? '') }}"></div></div>
              <div class="col-md-4"><div class="form-group"><label>إنستغرام</label><input type="text" name="social_instagram" class="form-control" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}"></div></div>
              <div class="col-md-4"><div class="form-group"><label>لينكدإن</label><input type="text" name="social_linkedin" class="form-control" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}"></div></div>
              <div class="col-md-4"><div class="form-group"><label>يوتيوب</label><input type="text" name="social_youtube" class="form-control" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}"></div></div>
              <div class="col-md-4"><div class="form-group"><label>واتساب</label><input type="text" name="social_whatsapp" class="form-control" value="{{ old('social_whatsapp', $settings['social_whatsapp'] ?? '') }}"></div></div>
            </div>

          </div>
          <div class="card-footer">
            <button class="btn btn-info">حفظ</button>
            <a class="btn btn-secondary" href="{{ route('admin.settings.index') }}">رجوع</a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  (function () {
    // منطق بناء القائمة: يزامن عناصر الواجهة إلى JSON داخل textarea#site_menu قبل الإرسال.
    const itemsContainer = document.getElementById('menu-items');
    const addBtn = document.getElementById('add-menu-item');
    const siteMenuInput = document.getElementById('site_menu');

    if (!itemsContainer || !addBtn || !siteMenuInput) {
      return;
    }

    function rowTemplate(label = '', url = '') {
      return `
        <div class="menu-item-row border rounded p-2 mb-2">
          <div class="row">
            <div class="col-md-5 mb-2 mb-md-0">
              <input type="text" class="form-control menu-label" placeholder="اسم العنصر (مثال: الرئيسية)" value="${label}">
            </div>
            <div class="col-md-6 mb-2 mb-md-0">
              <input type="text" class="form-control menu-url" placeholder="الرابط الكامل (https://...)" value="${url}">
            </div>
            <div class="col-md-1 d-flex align-items-center">
              <button type="button" class="btn btn-sm btn-outline-danger remove-menu-item w-100">&times;</button>
            </div>
          </div>
        </div>
      `;
    }

    function syncJson() {
      const rows = itemsContainer.querySelectorAll('.menu-item-row');
      const data = Array.from(rows).map((row) => {
        const label = (row.querySelector('.menu-label')?.value || '').trim();
        const url = (row.querySelector('.menu-url')?.value || '').trim();
        return { label, url };
      }).filter((item) => item.label && item.url);

      siteMenuInput.value = data.length ? JSON.stringify(data) : '';
    }

    function addRow(label = '', url = '') {
      itemsContainer.insertAdjacentHTML('beforeend', rowTemplate(label, url));
      syncJson();
    }

    function loadInitial() {
      let parsed = [];
      try {
        parsed = JSON.parse(siteMenuInput.value || '[]');
      } catch (e) {
        parsed = [];
      }

      if (Array.isArray(parsed) && parsed.length) {
        parsed.forEach((item) => addRow(item.label || '', item.url || ''));
      }
    }

    addBtn.addEventListener('click', function () {
      addRow();
    });

    itemsContainer.addEventListener('click', function (event) {
      if (!event.target.classList.contains('remove-menu-item')) {
        return;
      }

      const row = event.target.closest('.menu-item-row');
      if (row) {
        row.remove();
        syncJson();
      }
    });

    itemsContainer.addEventListener('input', function () {
      syncJson();
    });

    loadInitial();
  })();

  /**
   * مزامنة اختيار اللوحة الجاهزة مع حقول الألوان،
   * وأي تعديل يدوي على اللون يعيد الاختيار إلى "تخصيص يدوي" تلقائياً.
   */
  (function () {
    const primary = document.getElementById('theme_primary_color');
    const secondary = document.getElementById('theme_secondary_color');
    const accent = document.getElementById('theme_accent_color');
    if (!primary || !secondary || !accent) {
      return;
    }

    function applyFromLabel(labelEl) {
      if (!labelEl) {
        return;
      }
      const p = labelEl.getAttribute('data-preset-primary');
      const s = labelEl.getAttribute('data-preset-secondary');
      const a = labelEl.getAttribute('data-preset-accent');
      if (p && s && a) {
        primary.value = p;
        secondary.value = s;
        accent.value = a;
      }
    }

    document.querySelectorAll('input[name="theme_preset"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        if (radio.value === 'custom') {
          return;
        }
        const label = radio.closest('.theme-preset-option');
        applyFromLabel(label);
      });
    });

    [primary, secondary, accent].forEach(function (input) {
      input.addEventListener('input', function () {
        const custom = document.querySelector('input[name="theme_preset"][value="custom"]');
        if (custom) {
          custom.checked = true;
        }
      });
    });
  })();

  // مزامنة حقل HEX النصي مع input[type=color]
  (function () {
    const hexInputs = document.querySelectorAll('.js-color-hex');
    if (!hexInputs.length) {
      return;
    }

    function normalizeHex(val) {
      const v = (val || '').trim();
      if (!v) return '';
      return v.startsWith('#') ? v : ('#' + v);
    }

    hexInputs.forEach(function (hexInput) {
      const selector = hexInput.getAttribute('data-color-input');
      const colorInput = selector ? document.querySelector(selector) : null;
      if (!colorInput) {
        return;
      }

      colorInput.addEventListener('input', function () {
        hexInput.value = colorInput.value;
      });

      hexInput.addEventListener('input', function () {
        const normalized = normalizeHex(hexInput.value);
        if (/^#[0-9a-fA-F]{6}$/.test(normalized)) {
          colorInput.value = normalized;
        }
      });
    });
  })();
</script>
@endsection

