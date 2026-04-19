<?php

namespace App\Events;

use App\Models\Tender;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلق بعد حفظ مناقصة من لوحة التحكم لمزامنة خبر تلقائي مرتبط.
 */
class TenderSavedForNews
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Tender $tender) {}
}
