<?php

namespace App\Events;

use App\Models\Tender;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * حدث: تم حفظ مناقصة من لوحة التحكم (إنشاء أو تعديل).
 *
 * المستمع المرتبط: SyncAutoNewsFromTender → NewsAutomationService::syncFromTender()
 *
 * @see TenderController::store و ::update حيث يُستدعى event(new TenderSavedForNews(...))
 */
class TenderSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Tender $tender) {}
}
