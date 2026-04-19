<?php

namespace App\Listeners;

use App\Events\TenderSavedForNews;
use App\Services\NewsAutomationService;

/**
 * يحدّث أو ينشئ سجلًا في جدول news عند نشر/تعديل مناقصة.
 */
class SyncAutoNewsFromTender
{
    public function __construct(private NewsAutomationService $automation) {}

    public function handle(TenderSavedForNews $event): void
    {
        $this->automation->syncFromTender($event->tender->fresh());
    }
}
