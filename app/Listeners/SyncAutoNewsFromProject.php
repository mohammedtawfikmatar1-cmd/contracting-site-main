<?php

namespace App\Listeners;

use App\Events\ProjectSavedForNews;
use App\Services\NewsAutomationService;

/**
 * يحدّث أو ينشئ سجلًا في جدول news عند نشر/تعديل مشروع.
 */
class SyncAutoNewsFromProject
{
    public function __construct(private NewsAutomationService $automation) {}

    public function handle(ProjectSavedForNews $event): void
    {
        $this->automation->syncFromProject($event->project->fresh());
    }
}
