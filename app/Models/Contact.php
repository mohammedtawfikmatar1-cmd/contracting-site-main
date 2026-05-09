<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // الثوابت الخاصة بأنواع الطلبات باللغة العربية
    public const TYPE_GENERAL_AR = 'تواصل عام';
    public const TYPE_SERVICE_AR = 'طلب خدمة';
    public const TYPE_CAREER_AR = 'طلب توظيف';
    public const TYPE_TENDER_AR = 'عرض طلب';

    /**
     * الحقول القابلة للتعبئة (Mass Assignment).
     * تم تحديث المصفوفة لتشمل id العميل بدلاً من بياناته المباشرة.
     */
    protected $fillable = [
        'customer_id',
        'request_type',
        'service_requested',
        'message',
        'status',
    ];

    /**
     * تحويل أنواع البيانات تلقائياً.
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * تعريف علاقة "ينتمي إلى" (BelongsTo) مع موديل العميل (Customer).
     * تسمح بالوصول إلى بيانات العميل الذي قام بإنشاء هذا الطلب.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    /**
     * جلب اسم العميل وكأنه حقل في جدول Contact
     */
    public function getFullNameAttribute()
    {
        return $this->customer ? $this->customer->full_name : 'N/A';
    }

    /**
     * جلب بريد العميل وكأنه حقل في جدول Contact
     */
    public function getEmailAttribute()
    {
        return $this->customer ? $this->customer->email : 'N/A';
    }

    /**
     * جلب هاتف العميل وكأنه حقل في جدول Contact
     */
    public function getPhoneAttribute()
    {
        return $this->customer ? $this->customer->phone : 'N/A';
    }
    /**
     * نطاق استعلام (Scope) لجلب الطلبات التي حالتها "قيد الانتظار" فقط.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * نطاق استعلام (Scope) لفلترة الطلبات من نوع "طلب توظيف".
     * يبحث عن القيم بالإنجليزية أو العربية.
     */
    public function scopeCareers($query)
    {
        return $query->whereIn('request_type', ['career', self::TYPE_CAREER_AR]);
    }

    /**
     * نطاق استعلام (Scope) لفلترة الطلبات من نوع "طلب خدمة".
     * يبحث عن القيم بالإنجليزية أو العربية.
     */
    public function scopeServiceRequests($query)
    {
        return $query->whereIn('request_type', ['service', self::TYPE_SERVICE_AR]);
    }

    /**
     * سمة (Accessor) لتحويل كود نوع الطلب إلى نص مقروء باللغة العربية.
     * مثال: تحول 'service' إلى 'طلب خدمة'.
     */
    public function getRequestTypeLabelAttribute(): string
    {
        return match ($this->request_type) {
            'general' => self::TYPE_GENERAL_AR,
            'service' => self::TYPE_SERVICE_AR,
            'career' => self::TYPE_CAREER_AR,
            'tender' => self::TYPE_TENDER_AR,
            self::TYPE_GENERAL_AR,
            self::TYPE_SERVICE_AR,
            self::TYPE_CAREER_AR,
            self::TYPE_TENDER_AR => $this->request_type,
            default => (string) $this->request_type,
        };
    }

    /**
     * سمة (Accessor) لجلب رابط السيرة الذاتية الخاص بالعميل المرتبط بهذا الطلب.
     * تستخدم للحفاظ على توافق الكود في حال تم طلب ملف السيرة الذاتية مباشرة من موديل Contact.
     */
    public function getCvFileUrlAttribute()
    {
        return $this->customer ? $this->customer->cv_file_url : null;
    }

    /**
     * تحديث حالة الطلب لتصبح "قيد التنفيذ".
     */
    public function markAsInProgress()
    {
        return $this->update(['status' => 'in_progress']);
    }

    /**
     * تحديث حالة الطلب لتصبح "مكتمل".
     */
    public function markAsCompleted()
    {
        return $this->update(['status' => 'completed']);
    }

    /**
     * دالة تحقق (Boolean) لمعرفة ما إذا كان الطلب الحالي هو طلب توظيف.
     */
    public function isCareerApplication()
    {
        return $this->request_type === 'career';
    }
}