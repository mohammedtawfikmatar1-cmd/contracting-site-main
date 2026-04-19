<?php

namespace App\Listeners;

use App\Events\JobSavedForNews;
use App\Services\NewsAutomationService;

/**
 * يحدّث أو ينشئ سجلًا في جدول news عند تفعيل/تعديل وظيفة.
 */
class SyncAutoNewsFromJob
{
    public function __construct(private NewsAutomationService $automation) {}

    public function handle(JobSavedForNews $event): void
    {
        $this->automation->syncFromJob($event->job->fresh());
    }
}
