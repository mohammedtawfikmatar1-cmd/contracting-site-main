<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلق بعد حفظ مشروع من لوحة التحكم لمزامنة خبر تلقائي مرتبط.
 */
class ProjectSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Project $project) {}
}
