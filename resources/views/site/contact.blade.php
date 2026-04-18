@extends('site.layouts.app')

@section('title', 'شركة مقاولات - اتصل بنا')

@section('styles')
    @vite(['resources/css/contact.css'])
    <style>
        .contact-type-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--s4);
            margin-bottom: var(--s6);
        }
        .type-option {
            border: 2px solid #eee;
            padding: var(--s4);
            border-radius: var(--r-md);
            text-align: center;
            cursor: pointer;
            transition: all var(--tf);
        }
        .type-option i { display: block; font-size: 24px; margin-bottom: var(--s2); color: var(--ink-s); }
        .type-option span { font-weight: 700; font-size: var(--t-xs); }
        
        .type-option.active {
            border-color: var(--orange);
            background: var(--orange-subtle);
        }
        .type-option.active i { color: var(--orange); }
        
        .btn-submit {
            width: 100%;
            justify-content: center;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: var(--orange);
            color: white;
            border-radius: var(--r-md);
            font-weight: 800;
            cursor: pointer;
            border: none;
            transition: transform 0.3s;
        }
        .btn-submit:hover { transform: translateY(-3px); }

        /* ستايل حقل رفع الملف */
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 20px;
        }
        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: var(--r-md);
            cursor: pointer;
            transition: all 0.3s;
            color: var(--ink-s);
        }
        .file-upload-label:hover {
            border-color: var(--orange);
            background: var(--orange-subtle);
        }
        .file-upload-label i {
            font-size: 20px;
            color: var(--orange);
        }
        #cv_file {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
        .file-name-display {
            font-size: 14px;
            color: var(--ink);
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    @php
        $isAdminPreview = auth()->check();
    @endphp
    <section id="contact" class="contact section-py">
      <div class="container">
        <div class="sec-head reveal">
          <span class="sec-label">اتصل بنا</span>
          <h2>نحن هنا لمساعدتك</h2>
          <p>أرسل لنا رسالتك وسنتواصل معك في أقرب وقت</p>
        </div>
        <div class="contact-layout">
          <div class="contact-card reveal">
            <h3>معلومات التواصل</h3>
            <p class="cc-sub">نسعد بخدمتك على مدار أيام العمل</p>
            <ul class="ci-list" role="list">
              <!--
                بيانات التواصل: مصدرها إعدادات لوحة التحكم (branding)
                مثل company_address/company_phone/company_email وروابط الشبكات.
              -->
              <li>
                <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div><strong>العنوان</strong><span>{{ $siteSettings['company_address'] ?? $siteSettings['contact_address'] ?? ($isAdminPreview ? 'عنوان توضيحي: أضف عنوان الشركة من لوحة التحكم' : '') }}</span></div>
              </li>
              <li>
                <div class="ci-icon"><i class="fas fa-phone-alt"></i></div>
                <div><strong>الهاتف</strong><span>{{ $siteSettings['company_phone'] ?? $siteSettings['contact_phone'] ?? ($isAdminPreview ? '+0000000000' : '') }}</span></div>
              </li>
              <li>
                <div class="ci-icon"><i class="fas fa-envelope"></i></div>
                <div><strong>البريد الإلكتروني</strong><span>{{ $siteSettings['company_email'] ?? $siteSettings['contact_email'] ?? ($isAdminPreview ? 'info@company.com' : '') }}</span></div>
              </li>
              <li>
                <div class="ci-icon"><i class="fas fa-clock"></i></div>
                <div><strong>أوقات العمل</strong><span>السبت — الخميس: 8ص — 6م</span></div>
              </li>
            </ul>
            <div class="cc-social" aria-label="تواصل اجتماعي">
              <a href="{{ $siteSettings['social_facebook'] ?? ($isAdminPreview ? 'https://facebook.com/your-company' : '#') }}" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
              <a href="{{ $siteSettings['social_x'] ?? ($isAdminPreview ? 'https://x.com/your-company' : '#') }}" aria-label="X"><i class="fab fa-x-twitter"></i></a>
              <a href="{{ $siteSettings['social_instagram'] ?? ($isAdminPreview ? 'https://instagram.com/your-company' : '#') }}" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
              <a href="{{ $siteSettings['social_linkedin'] ?? ($isAdminPreview ? 'https://linkedin.com/company/your-company' : '#') }}" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
          </div>

          <div class="contact-form reveal" style="--delay:150ms">
            <!--
              نموذج التواصل المتقدم:
              - يرسل البيانات إلى route('contact.store') => ContactRequestController@storeGeneral
              - يتم حفظ الطلب في جدول contacts ثم إرسال إشعار للإدارة (Notifications).
            -->
            <form id="advancedContactForm" action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h3>أرسل رسالتك</h3>
                
                <div class="contact-type-selector">
                    <!-- اختيار نوع الطلب: عام/خدمة/توظيف (يحدد request_type الذي يصل للمتـحكم) -->
                    <div class="type-option active" data-type="general">
                        <i class="fas fa-comment-dots"></i>
                        <span>استفسار عام</span>
                    </div>
                    <div class="type-option" data-type="service">
                        <i class="fas fa-hard-hat"></i>
                        <span>طلب خدمة</span>
                    </div>
                    <div class="type-option" data-type="career">
                        <i class="fas fa-user-tie"></i>
                        <span>طلب توظيف</span>
                    </div>
                </div>

                <input type="hidden" name="request_type" id="request_type" value="general">

                <div class="frow">
                  <div class="fg">
                    <label for="fullName">الاسم الكامل</label>
                    <input id="fullName" name="full_name" type="text" placeholder="أدخل اسمك الكامل" required />
                  </div>
                  <div class="fg">
                    <label for="phone">رقم الهاتف</label>
                    <input id="phone" name="phone" type="tel" placeholder="+967..." required />
                  </div>
                </div>

                <div class="fg" id="service_select_group" style="display: none; margin-bottom: 15px;">
                  <label for="service">الخدمة المطلوبة</label>
                  <select id="service" name="service" style="width: 100%; padding: 12px; border-radius: var(--r-md); border: 1px solid #ddd;">
                    <!-- بداية خيارات الخدمات: $services قادمة من SiteController@contact (مُدارة من لوحة التحكم: الخدمات) -->
                    @forelse($services as $service)
                        <option value="{{ $service->title }}">{{ $service->title }}</option>
                    @empty
                        <option value="general">خدمة عامة</option>
                    @endforelse
                    <!-- نهاية خيارات الخدمات -->
                  </select>
                </div>

                <!-- حقل رفع السيرة الذاتية (يظهر فقط عند اختيار طلب توظيف) -->
                <div class="fg" id="cv_upload_group" style="display: none; margin-bottom: 15px;">
                  <label for="cv_file">السيرة الذاتية (PDF فقط)</label>
                  <div class="file-upload-wrapper">
                    <label for="cv_file" class="file-upload-label">
                      <i class="fas fa-file-pdf"></i>
                      <span class="file-name-display">اختر ملف PDF...</span>
                    </label>
                    <input type="file" id="cv_file" name="cv_file" accept=".pdf">
                  </div>
                </div>

                <div class="fg">
                  <label for="email">البريد الإلكتروني</label>
                  <input id="email" name="email" type="email" placeholder="example@mail.com" required />
                </div>
                <div class="fg">
                  <label for="message">رسالتك</label>
                  <textarea id="message" name="message" placeholder="أخبرنا عن طلبك..." rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-submit">
                  <span>إرسال الرسالة</span>
                  <i class="fas fa-paper-plane" aria-hidden="true"></i>
                </button>
                <p class="form-note"><i class="fas fa-lock" aria-hidden="true"></i> بياناتك محفوظة بأمان تام</p>
            </form>
          </div>
        </div>
      </div>
    </section>
@endsection

@section('scripts')
<script>
    // منطق واجهة المستخدم: تبديل نوع الطلب وإظهار/إخفاء حقول إضافية قبل الإرسال إلى المتحكم.
    document.querySelectorAll('.type-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            const type = this.getAttribute('data-type');
            document.getElementById('request_type').value = type;
            
            const serviceGroup = document.getElementById('service_select_group');
            const cvGroup = document.getElementById('cv_upload_group');
            const cvInput = document.getElementById('cv_file');
            const serviceInput = document.getElementById('service');
            
            // إظهار/إخفاء حقل الخدمات
            if (type === 'service') {
                serviceGroup.style.display = 'block';
                serviceInput.setAttribute('name', 'service_requested');
            } else {
                serviceGroup.style.display = 'none';
                serviceInput.setAttribute('name', 'service');
            }

            // إظهار/إخفاء حقل السيرة الذاتية
            if (type === 'career') {
                cvGroup.style.display = 'block';
                cvInput.required = true;
            } else {
                cvGroup.style.display = 'none';
                cvInput.required = false;
            }
            
            const messageLabel = document.querySelector('label[for="message"]');
            if (type === 'career') {
                messageLabel.textContent = 'نبذة عن خبراتك ومؤهلاتك';
            } else {
                messageLabel.textContent = 'رسالتك';
            }
        });
    });

    // تحديث اسم الملف المختار في الواجهة
    document.getElementById('cv_file').addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'اختر ملف PDF...';
        document.querySelector('.file-name-display').textContent = fileName;
    });
</script>
@endsection
