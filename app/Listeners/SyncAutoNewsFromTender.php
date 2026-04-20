<?php

namespace App\Listeners;

use App\Events\TenderSavedForNews;
use App\Services\NewsAutomationService;

/**
 * مستمع لمزامنة الأخبار التلقائية بعد حفظ مناقصة.
 *
 * الفكرة نفسها: Event → handle() → NewsAutomationService (انظر تعليق SyncAutoNewsFromProject).
 */
class SyncAutoNewsFromTender
{
    public function __construct(private NewsAutomationService $automation) {}

    public function handle(TenderSavedForNews $event): void
    {
        $this->automation->syncFromTender($event->tender->fresh());
    }
}
