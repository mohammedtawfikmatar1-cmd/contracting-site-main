<?php

/**
 * الغرض من الملف:
 * تمثيل كيان "المناقصة" وإدارة حالتها وتاريخ الإغلاق.
 *
 * التبعية:
 * App\Models\Tender ضمن Eloquent Models.
 *
 * المكونات الأساسية:
 * - Scopes للتصفية حسب النشر والحالة ونوع العمل.
 * - علاقة اختيارية مع المشروع.
 *
 * خريطة تدفق البيانات:
 * تُدار المناقصات من قسم "المناقصات" في لوحة التحكم،
 * وتظهر في الواجهة الأمامية بحسب حالة النشر وموعد الإغلاق.
 */
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;
    use HasUniqueSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'work_type',
        'location',
        'closing_date',
        'status',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'closing_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Tender $tender): void {
            $tender->news()->delete();
        });
    }

    /**
     * أخبار تلقائية مرتبطة بهذه المناقصة (مزامنة من لوحة التحكم).
     */
    public function news()
    {
        return $this->morphMany(News::class, 'newsable');
    }

    /**
     * Scope a query to only include published tenders.
     */
    public function scopePublished($query)
    {
        // المناقصات المسموح بعرضها للزوار.
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include open tenders.
     */
    public function scopeOpen($query)
    {
        // المناقصات المفتوحة فعليا (حالة open ولم ينته تاريخ الإغلاق).
        return $query->where('status', 'open')
                     ->where('closing_date', '>=', now());
    }

    /**
     * Scope a query to filter tenders by work type.
     */
    public function scopeOfWorkType($query, string $type)
    {
        // تصفية المناقصات حسب نوع الأعمال المطلوبة.
        return $query->where('work_type', $type);
    }

    /**
     * Check if the tender is still open for submission.
     *
     * @return bool
     */
    public function isOpen()
    {
        // التحقق البرمجي من أحقية استقبال عروض جديدة.
        return $this->status === 'open' && $this->closing_date->isFuture();
    }

    /**
     * Get days remaining until the closing date.
     *
     * @return int
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->closing_date->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->closing_date);
    }

    /**
     * العلاقة: كل مناقصة قد تنتمي إلى مشروع واحد (Many to One).
     * إذا كانت null فهي مناقصة عامة غير مرتبطة بمشروع محدد.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
