<footer role="contentinfo">
    <div class="footer-glow" aria-hidden="true"></div>
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <!--
                    خريطة تدفق البيانات (Footer):
                    - اسم الشركة والشعارات والصورة: تأتي من إعدادات لوحة التحكم (branding).
                    - موجز الفوتر: footer_brief / footer_about من الإعدادات.
                    - روابط سريعة: من site_menu في لوحة التحكم.
                    - بيانات التواصل وروابط السوشيال: من إعدادات الإدارة.
                -->
                <a href="{{ route('home') }}" class="footer-logo">
                    <span class="logo-name">{{ $settingsValue(['company_name', 'site_name'], 'شركة مقاولات نموذجية', '') }}</span>
                    <img src="{{ $settingsMedia(['logo_transparent', 'logo_main'], asset('building.png'), asset('building.png')) }}" alt="{{ $settingsValue('company_name', 'شعار الشركة', 'شعار الموقع') }}">
                </a>
                <p>{{ $settingsValue(['footer_brief', 'footer_about'], 'نبذة تعريفية: أضف وصفًا مختصرًا عن شركتك من لوحة التحكم.', '') }}</p>
                @php
                    $footerCompanyImage = $siteSettings['footer_image'] ?? $siteSettings['about_main_image'] ?? null;
                @endphp
                @if(!empty($footerCompanyImage))
                    <img src="{{ $footerCompanyImage }}" alt="صورة الشركة" class="company-image" />
                @elseif(auth()->check())
                    <div class="company-image" style="
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        min-height:120px;
                        border:1px dashed rgba(255,255,255,.35);
                        color:rgba(255,255,255,.8);
                        font-size:13px;
                        text-align:center;
                        padding:10px;
                    ">
                        أضف صورة الفوتر من لوحة التحكم &gt; الهوية البصرية
                    </div>
                @endif
            </div>
            <nav class="footer-nav" aria-label="روابط سريعة">
                <h4>روابط سريعة</h4>
                <ul role="list">
                    <!-- بداية روابط سريعة (قادمة من إعدادات الإدارة: site_menu) -->
                    @foreach(($siteMenu ?? collect())->take(12) as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                    <!-- نهاية روابط سريعة -->
                </ul>
                @if(isset($sitePages) && $sitePages->isNotEmpty())
                    <!-- الصفحات الثابتة من لوحة التحكم — مجموعة منفصلة بعنوان واضح كي لا تختلط مع الروابط الرئيسية -->
                    <h4 class="footer-nav__subheading">صفحات إضافية</h4>
                    <ul class="footer-nav__sublist" role="list">
                        @foreach($sitePages as $page)
                            <li><a href="{{ route('pages.show', $page->slug) }}">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </nav>
            <div class="footer-contact">
                <h4>تواصل معنا</h4>
                <a href="tel:{{ $settingsValue('company_phone', '+0000000000', '') }}" class="fc-link"><i class="fas fa-phone-alt"></i>{{ $settingsValue('company_phone', '+0000000000', '') }}</a>
                @php
                    $phone2 = $settingsValue('company_phone_2', '', '');
                @endphp
                @if(filled($phone2))
                    <a href="tel:{{ $phone2 }}" class="fc-link"><i class="fas fa-phone"></i>{{ $phone2 }}</a>
                @endif
                <a href="mailto:{{ $settingsValue('company_email', 'info@company.com', '') }}" class="fc-link"><i class="fas fa-envelope"></i>{{ $settingsValue('company_email', 'info@company.com', '') }}</a>
                @php
                    $whatsapp = $settingsValue('social_whatsapp', '', '');
                @endphp
                @if(filled($whatsapp))
                    <a href="{{ $whatsapp }}" class="fc-link" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i>
                        واتساب
                    </a>
                @endif
                <div class="social-links" aria-label="تواصل اجتماعي">
                    <a href="{{ $settingsValue('social_facebook', 'https://facebook.com/your-company', '#') }}" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="{{ $settingsValue('social_x', 'https://x.com/your-company', '#') }}" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                    <a href="{{ $settingsValue('social_instagram', 'https://instagram.com/your-company', '#') }}" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="{{ $settingsValue('social_linkedin', 'https://linkedin.com/company/your-company', '#') }}" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} {{ $settingsValue('copyright_name', 'اسم الشركة', '') }}. جميع الحقوق محفوظة.</p>
            <a href="#home" class="back-top" aria-label="العودة لأعلى"><i class="fas fa-chevron-up"></i></a>
        </div>
    </div>
</footer>
