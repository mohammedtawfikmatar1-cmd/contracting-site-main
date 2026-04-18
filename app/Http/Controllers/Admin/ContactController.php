<?php

/**
 * الغرض من الملف:
 * إدارة طلبات التواصل المخزنة في النظام ومتابعة حالتها من لوحة التحكم.
 *
 * التبعية:
 * App\Http\Controllers\Admin\ContactController.
 *
 * المكونات الأساسية:
 * - عرض الرسائل.
 * - فتح تفاصيل الطلب.
 * - تحديث حالة الطلب أثناء المعالجة.
 *
 * خريطة تدفق البيانات:
 * الطلبات تأتي من نماذج الواجهة الأمامية عبر ContactRequestController،
 * ثم تظهر هنا لتتم مراجعتها وتحويل حالتها من جديد إلى قيد المعالجة أو غير ذلك.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * عرض قائمة طلبات التواصل مع دعم البحث.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $contacts = Contact::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', compact('contacts', 'q'));
    }

    /**
     * عرض تفاصيل طلب واحد ليسهل على الإدارة مراجعته والرد عليه خارجيًا.
     */
    public function show(Contact $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    /**
     * عند فتح الطلب أو التعامل معه لأول مرة، يتم نقله من pending إلى in_progress.
     */
    public function markAsRead(Contact $contact)
    {
        if ($contact->status === 'pending') {
            $contact->update(['status' => 'in_progress']);
        }

        return redirect()->route('admin.contacts.show', $contact)->with('success', 'تم تحديث حالة الرسالة.');
    }

    /**
     * حذف طلب من لوحة التحكم.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'تم حذف الرسالة بنجاح.');
    }
}
