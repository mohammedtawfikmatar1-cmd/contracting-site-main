<?php

namespace App\Events;

use App\Models\Job;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلق بعد حفظ وظيفة من لوحة التحكم لمزامنة خبر تلقائي مرتبط.
 */
class JobSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Job $job) {}
}
