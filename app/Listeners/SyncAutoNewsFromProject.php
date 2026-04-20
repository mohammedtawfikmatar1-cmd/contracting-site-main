<?php

namespace App\Listeners;

use App\Events\ProjectSavedForNews;
use App\Services\NewsAutomationService;

/**
 * مستمع (Listener) لحدث ProjectSavedForNews
 *
 * ماذا يحدث خطوة بخطوة؟
 * ------------------------
 * 1) Laravel يستدعي handle() تلقائيًا بعد event(new ProjectSavedForNews($project))
 * 2) نحقن NewsAutomationService عبر المعامل في الـ constructor (حقن تبعية)
 * 3) نستدعي fresh() على المشروع لقراءة أحدث بيانات من قاعدة البيانات بعد الحفظ
 * 4) الخدمة تنشئ/تحدّث/تحذف سجل الأخبار المرتبط
 */
class SyncAutoNewsFromProject
{
    public function __construct(private NewsAutomationService $automation) {}

    public function handle(ProjectSavedForNews $event): void
    {
        $this->automation->syncFromProject($event->project->fresh());
    }
}
