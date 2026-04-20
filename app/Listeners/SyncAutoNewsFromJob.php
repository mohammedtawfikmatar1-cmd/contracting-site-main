<?php

namespace App\Listeners;

use App\Events\JobSavedForNews;
use App\Services\NewsAutomationService;

/**
 * مستمع لمزامنة الأخبار التلقائية بعد حفظ وظيفة.
 */
class SyncAutoNewsFromJob
{
    public function __construct(private NewsAutomationService $automation) {}

    public function handle(JobSavedForNews $event): void
    {
        $this->automation->syncFromJob($event->job->fresh());
    }
}
