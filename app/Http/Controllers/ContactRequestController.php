<?php

namespace App\Http\Controllers;

use App\Events\ContactRequestSubmitted;
use App\Models\Contact;
use App\Models\Customer; // تم إضافة موديل العميل هنا
use App\Models\Job;
use App\Models\Service;
use App\Models\Tender;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    /**
     * معالجة نموذج التواصل العام في الموقع.
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

        // تحديد نوع الطلب بالعربي
        $requestType = match ($validated['request_type'] ?? 'general') {
            'service' => Contact::TYPE_SERVICE_AR,
            'career' => Contact::TYPE_CAREER_AR,
            default => Contact::TYPE_GENERAL_AR,
        };

        // معالجة الملف في حال وجوده
        $cvFilePath = null;
        if ($request->hasFile('cv_file')) {
            $cvFilePath = $request->file('cv_file')->store('cv-files', 'public');
        }

        // إيجاد العميل أو إنشائه
        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'cv_file' => $cvFilePath,
            ]
        );

        // تحديث الملف إذا قام العميل برفع ملف جديد
        if ($cvFilePath && $customer->cv_file !== $cvFilePath) {
            $customer->update(['cv_file' => $cvFilePath]);
        }

        // إنشاء الطلب وربطه بالعميل
        $contact = $customer->contacts()->create([
            'request_type' => $requestType,
            'service_requested' => $validated['service_requested'] ?? null,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم استلام طلبك بنجاح، وسيتم التواصل معك قريبًا.');
    }

    /**
     * استقبال طلب خدمة مرتبط بخدمة محددة.
     */
    public function storeServiceRequest(Request $request, Service $service)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $email = $validated['email'] ?? 'unknown@example.com';

        // إيجاد العميل أو إنشائه
        $customer = Customer::firstOrCreate(
            ['email' => $email],
            [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
            ]
        );

        // إنشاء الطلب وربطه بالعميل
        $contact = $customer->contacts()->create([
            'request_type' => Contact::TYPE_SERVICE_AR,
            'service_requested' => $service->title,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم إرسال طلب الخدمة بنجاح.');
    }

    /**
     * استقبال طلبات التوظيف من صفحة الوظيفة.
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

        // رفع ملف السيرة الذاتية
        $cvFilePath = $request->file('cv_file')->store('cv-files', 'public');

        // إيجاد العميل أو إنشائه
        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'cv_file' => $cvFilePath,
            ]
        );

        // تحديث السيرة الذاتية إن تم رفع واحدة جديدة
        if ($cvFilePath && $customer->cv_file !== $cvFilePath) {
            $customer->update(['cv_file' => $cvFilePath]);
        }

        // إنشاء الطلب وربطه بالعميل
        $contact = $customer->contacts()->create([
            'request_type' => Contact::TYPE_CAREER_AR,
            'service_requested' => $job->title,
            'message' => $validated['message'] ?? ('طلب توظيف على وظيفة: ' . $job->title),
            'status' => 'pending',
        ]);

        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم إرسال طلب التوظيف بنجاح.');
    }

    /**
     * استقبال عروض/طلبات المناقصات.
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

        // معالجة ملف عرض المناقصة
        $proposalFilePath = null;
        if ($request->hasFile('proposal_file')) {
            $proposalFilePath = $request->file('proposal_file')->store('tender-proposals', 'public');
        }

        // إيجاد العميل أو إنشائه (نحفظ ملف المناقصة في حقل cv_file للحفاظ على البنية القديمة كما طلبت)
        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'cv_file' => $proposalFilePath, 
            ]
        );

        // تحديث الملف إذا تم رفع عرض جديد
        if ($proposalFilePath && $customer->cv_file !== $proposalFilePath) {
            $customer->update(['cv_file' => $proposalFilePath]);
        }

        // إنشاء الطلب وربطه بالعميل
        $contact = $customer->contacts()->create([
            'request_type' => Contact::TYPE_TENDER_AR,
            'service_requested' => $tender->title,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        event(new ContactRequestSubmitted($contact));

        return back()->with('success', 'تم إرسال عرض المناقصة بنجاح.');
    }
}