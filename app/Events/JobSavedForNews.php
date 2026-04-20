<?php

namespace App\Events;

use App\Models\Job;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * حدث: تم حفظ وظيفة من لوحة التحكم (إنشاء أو تعديل).
 *
 * المستمع المرتبط: SyncAutoNewsFromJob → NewsAutomationService::syncFromJob()
 *
 * ملاحظة: ظهور الوظيفة للزوار يعتمد على is_active؛ والخبر التلقائي يُزال إذا عُطّلت الوظيفة.
 */
class JobSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Job $job) {}
}
