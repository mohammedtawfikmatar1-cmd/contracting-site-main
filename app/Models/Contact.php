<?php

/**
 * الغرض من الملف:
 * تمثيل طلبات التواصل الواردة من نماذج الموقع الأمامي.
 *
 * التبعية:
 * App\Models\Contact ضمن نماذج قاعدة البيانات.
 *
 * المكونات الأساسية:
 * - Scopes لتصفية الطلبات حسب النوع والحالة.
 * - Accessor لملف السيرة الذاتية.
 *
 * خريطة تدفق البيانات:
 * الزائر يرسل الطلب من نموذج الواجهة، فيُخزّن هنا ويظهر للإدارة
 * في قسم "طلبات التواصل" أو "طلبات التوظيف" بحسب نوع الطلب.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Contact extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'request_type',
        'service_requested',
        'cv_file',
        'message',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Scope a query to only include pending contacts.
     */
    public function scopePending($query)
    {
        // الطلبات الجديدة التي لم يبدأ التعامل معها بعد.
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include career-related requests.
     */
    public function scopeCareers($query)
    {
        // تصفية طلبات التوظيف فقط.
        return $query->where('request_type', 'career');
    }

    /**
     * Scope a query to only include service-related requests.
     */
    public function scopeServiceRequests($query)
    {
        // تصفية الطلبات المرتبطة بطلب خدمة من الزائر.
        return $query->where('request_type', 'service');
    }

    /**
     * Get the full URL for the CV file.
     *
     * @return string|null
     */
    public function getCvFileUrlAttribute()
    {
        // إنشاء رابط قابل للاستخدام لتحميل ملف السيرة الذاتية.
        return $this->cv_file ? Storage::url($this->cv_file) : null;
    }

    /**
     * Mark the contact request as in progress.
     *
     * @return bool
     */
    public function markAsInProgress()
    {
        // تحديث حالة الطلب عند بدء المعالجة من الإدارة.
        return $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark the contact request as completed.
     *
     * @return bool
     */
    public function markAsCompleted()
    {
        // تحديث حالة الطلب بعد الانتهاء من معالجته.
        return $this->update(['status' => 'completed']);
    }

    /**
     * Check if the request is a career application.
     *
     * @return bool
     */
    public function isCareerApplication()
    {
        // فحص سريع لتحديد ما إذا كان الطلب مرتبطا بالتوظيف.
        return $this->request_type === 'career';
    }
}
