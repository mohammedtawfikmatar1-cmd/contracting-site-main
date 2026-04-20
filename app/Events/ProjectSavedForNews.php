<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * حدث: تم حفظ مشروع من لوحة التحكم (إنشاء أو تعديل).
 *
 * لماذا نستخدمه؟
 * ----------------
 * في Laravel، الـ Event مجرد "إشعار" بأن شيئًا حدث. لا يضع منطقًا معقدًا هنا؛
 * المنطق يجلس في Listener (المستمع) الذي يسجّل في AppServiceProvider.
 *
 * من يستمع؟
 * ----------
 * SyncAutoNewsFromProject → يستدعي NewsAutomationService::syncFromProject()
 *
 * متى يُطلق؟
 * -----------
 * من ProjectController بعد create و update (انظر أسطر event(new ...)).
 */
class ProjectSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Project $project) {}
}
