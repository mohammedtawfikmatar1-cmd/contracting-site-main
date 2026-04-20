<?php

/**
 * الغرض من الملف:
 * تمثيل الوظائف الشاغرة المنشورة في الموقع.
 *
 * التبعية:
 * App\Models\Job ضمن نماذج Eloquent.
 *
 * المكونات الأساسية:
 * - ربط الموديل بجدول job_posts.
 * - Scopes لعرض الوظائف النشطة والقابلة للنشر.
 *
 * خريطة تدفق البيانات:
 * قسم "الوظائف" في لوحة التحكم يتحكم في هذا المحتوى،
 * وتظهر الوظائف النشطة فقط في صفحة التوظيف بالواجهة الأمامية.
 */
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    use HasUniqueSlug;

    // ربط هذا الموديل بجدول الوظائف الفعلي في قاعدة البيانات.
    protected $table = 'job_posts';

    protected $fillable = [
        'title',
        'location',
        'type',
        'experience',
        'qualification',
        'description',
        'requirements',
        'skills',
        'is_active',
        'closing_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requirements' => 'array',
        'skills' => 'array',
        'closing_date' => 'date',
    ];

    /** حذف الوظيفة يزيل الخبر التلقائي المرتبط بها. */
    protected static function booted(): void
    {
        static::deleting(function (Job $job): void {
            $job->news()->delete();
        });
    }

    /**
     * أخبار تلقائية من جدول job_posts — راجع NewsAutomationService::syncFromJob.
     */
    public function news()
    {
        return $this->morphMany(News::class, 'newsable');
    }

    /**
     * إرجاع الوظائف المفعلة والتي لم ينته تاريخ التقديم عليها.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('closing_date')
                  ->orWhere('closing_date', '>=', now());
            });
    }

    /**
     * نطاق جاهز لعرض الوظائف في الواجهة الأمامية بترتيب الأحدث.
     */
    public function scopePublished($query)
    {
        return $query->active()->latest();
    }
}