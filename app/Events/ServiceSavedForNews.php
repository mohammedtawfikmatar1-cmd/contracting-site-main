<?php

/**
 * ServiceSavedForNews
 * ------------------
 * حدث يُطلق عند حفظ (إنشاء/تعديل) خدمة من لوحة التحكم.
 *
 * الهدف:
 * - تمكين "الأخبار التلقائية" لخدمات الموقع كما هو الحال مع المشاريع/المناقصات/الوظائف.
 *
 * تدفق العمل:
 * Admin\ServiceController@store|update
 *   → event(new ServiceSavedForNews($service))
 *   → Listener: SyncAutoNewsFromService
 *   → Service: NewsAutomationService@syncFromService
 *   → جدول news (خبر تلقائي يظهر في الواجهة)
 */

namespace App\Events;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Service $service)
    {
    }
}

