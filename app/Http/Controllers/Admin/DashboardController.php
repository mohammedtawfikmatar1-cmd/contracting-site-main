<?php

/**
 * الغرض من الملف:
 * تجهيز الصفحة الرئيسية للوحة التحكم بالإحصاءات والبيانات السريعة.
 *
 * التبعية:
 * App\Http\Controllers\Admin\DashboardController.
 *
 * المكونات الأساسية:
 * - إحصاءات سريعة من الجداول الرئيسية.
 * - آخر الرسائل وآخر المشاريع لعرض ملخص تشغيلي.
 *
 * خريطة تدفق البيانات:
 * هذه الصفحة لا تُنشئ بيانات جديدة، لكنها تلخص ما أُدخل في أقسام الإدارة
 * وتساعد المشرف على متابعة ما سينعكس في الواجهة أو ما يحتاج إلى معالجة.
 *
 * تلميح: العدّادات هنا قد تشمل أخبارًا يدوية + أخبارًا تلقائية (كلها في جدول news).
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Job;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\Tender;

class DashboardController extends Controller
{
    /**
     * بناء بيانات لوحة القيادة اعتمادا على محتوى النظام الحالي.
     */
    public function index()
    {
        $stats = [
            'projects' => Project::count(),
            'services' => Service::count(),
            'new_messages' => Contact::where('status', 'pending')->count(),
            'active_tenders' => Tender::where('status', 'open')->where('closing_date', '>=', now())->count(),
            'news' => News::count(),
            'pages' => Page::count(),
            'jobs' => Job::count(),
        ];

        $latestContacts = Contact::latest()->limit(5)->get();
        $latestProjects = Project::latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'latestContacts', 'latestProjects'));
    }
}
