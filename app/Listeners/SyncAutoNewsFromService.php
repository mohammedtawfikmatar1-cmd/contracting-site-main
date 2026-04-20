<?php

/**
 * SyncAutoNewsFromService
 * ----------------------
 * مستمع (Listener) لمزامنة خبر تلقائي من بيانات "الخدمة".
 *
 * لماذا Listener؟
 * - لعزل منطق الأخبار التلقائية عن متحكمات الإدارة.
 * - ليسهل لاحقًا نقل التنفيذ إلى Queue إن لزم (بدون تغيير التدفق).
 *
 * تدفق العمل:
 * ServiceSavedForNews → هذا المستمع → NewsAutomationService@syncFromService
 */

namespace App\Listeners;

use App\Events\ServiceSavedForNews;
use App\Services\NewsAutomationService;

class SyncAutoNewsFromService
{
    public function __construct(private readonly NewsAutomationService $newsAutomation)
    {
    }

    public function handle(ServiceSavedForNews $event): void
    {
        $this->newsAutomation->syncFromService($event->service);
    }
}

