<?php

/**
 * الغرض من الملف:
 * استقبال طلبات التواصل الواردة من الواجهة الأمامية بمختلف أنواعها
 * (عام، طلب خدمة، طلب توظيف، وطلب/عرض مناقصة).
 *
 * التبعية:
 * App\Http\Controllers\ContactRequestController ضمن طبقة Controllers.
 *
 * المكونات الأساسية:
 * - Contact لتخزين الطلبات.
 * - ContactRequestSubmitted event لإطلاق الإشعارات بعد الحفظ.
 * - رفع الملفات إلى التخزين العام عند وجود مرفقات.
 *
 * خريطة تدفق البيانات (للمبتدئين):
 * -------------------------
 * [نموذج في المتصفح] → POST إلى أحد المسارات في routes/web.php
 * → دالة هنا (storeGeneral / storeServiceRequest / ...)
 * → Contact::create(...)
 * → event(new ContactRequestSubmitted(...))
 * → SendAdminContactNotification يرسل إشعارًا للمستخدمين في الإدارة
 */
namespace App\Http\Controllers;

use App\Events\ContactRequestSubmitted;
use App\Models\Contact;
use App\Models\Job;
use App\Models\Service;
use App\Models\Tender;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    /**
     * معالجة نموذج التواصل العام في الموقع.
     * هذا المسار يغذي قسم الرسائل/التواصل داخل لوحة الإدارة.
     */
    public function storeGeneral(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string'],
            'request_type' => ['nullable', 'in:general,service,career'],
            'service_requested' => ['nullable', 'string', 'max:255'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $validated['request_type'] = $validated['request_type'] ?? 'general';
        $validated['status'] = 'pending';

        if ($request->hasFile('cv_file')) {
            // في بعض النماذج العامة قد يُرفق ملف PDF، لذا يتم حفظه داخل التخزين العام.
            $validated['cv_file'] = $request->file('cv_file')->store('cv-files', 'public');
        }

        // إنشاء سجل الطلب ثم إطلاق حدث لإشعار الإدارة بوجود طلب جديد.
        $contact = Contact::create($validated);
        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم استلام طلبك بنجاح، وسيتم التواصل معك قريبًا.');
    }

    /**
     * استقبال طلب خدمة مرتبط بخدمة محددة من صفحة تفاصيل الخدمة.
     * اسم الخدمة يُخزن داخل service_requested حتى يسهل على الإدارة معرفة المصدر.
     */
    public function storeServiceRequest(Request $request, Service $service)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        // ربط الطلب بالخدمة المختارة في الواجهة لتظهر بوضوح في لوحة التحكم.
        $contact = Contact::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? 'unknown@example.com',
            'request_type' => 'service',
            'service_requested' => $service->title,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);
        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم إرسال طلب الخدمة بنجاح.');
    }

    /**
     * استقبال طلبات التوظيف من صفحة الوظيفة.
     * السيرة الذاتية مرفق أساسي وتُرفع إلى مسار منفصل لتسهيل إدارتها.
     */
    public function storeJobApplication(Request $request, Job $job)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string'],
            'cv_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        // يتم تحويل الطلب إلى سجل تواصل من نوع career حتى يظهر ضمن نفس دورة المتابعة الإدارية.
        $contact = Contact::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'request_type' => 'career',
            'service_requested' => $job->title,
            'cv_file' => $request->file('cv_file')->store('job-applications', 'public'), // حفظ ملف السيرة الذاتية.
            'message' => $validated['message'] ?? ('طلب توظيف على وظيفة: ' . $job->title),
            'status' => 'pending',
        ]);
        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم إرسال طلب التوظيف بنجاح.');
    }

    /**
     * استقبال عروض/طلبات المناقصات من صفحة المناقصة الأمامية.
     * يتم تخزين الملف المرفق داخل cv_file لإعادة استخدام بنية جدول contacts الحالية.
     */
    public function storeTenderRequest(Request $request, Tender $tender)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string'],
            'proposal_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        // يُحفظ العرض كمراسلة مرتبطة بالمناقصة لتظهر للإدارة ضمن صندوق الطلبات.
        $contact = Contact::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'request_type' => 'service',
            'service_requested' => 'Tender: ' . $tender->title,
            'cv_file' => $request->hasFile('proposal_file')
                ? $request->file('proposal_file')->store('tender-proposals', 'public') // رفع ملف العرض الفني/المالي إن وُجد.
                : null,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);
        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم إرسال عرض المناقصة بنجاح.');
    }
}
