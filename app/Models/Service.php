<?php

/**
 * الغرض من الملف:
 * تمثيل كيان "الخدمة" في النظام، وهو الأساس لتصنيف مشاريع الشركة.
 *
 * التبعية:
 * App\Models\Service ضمن طبقة Eloquent ORM.
 *
 * المكونات الأساسية:
 * - HasUniqueSlug لتوليد slug فريد.
 * - HasTranslations لدعم الحقول متعددة اللغات.
 *
 * خريطة تدفق البيانات:
 * بيانات الخدمة تُنشأ وتُحدّث من قسم "الخدمات" في لوحة التحكم،
 * ثم تُستخدم في الواجهة الأمامية لعرض قائمة الخدمات وربط كل خدمة بمشاريعها.
 */
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    use HasFactory;
    use HasUniqueSlug;
    use HasTranslations;

    public array $translatable = [
        'title',
        'description',
        'overview',
    ];

    protected $fillable = [
        'title',
        'description',
        'image',
        'icon',           // أيقونة الخدمة (مثلاً fas fa-city)
        'overview',       // نظرة عامة مفصلة
        'achievements',   // إنجازات (JSON قائمة)
        'is_published',
        'sort_order',     // ترتيب الظهور
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'achievements' => 'array',
        'sort_order' => 'integer',
    ];

    public function scopePublished($query)
    {
        // إرجاع الخدمات المنشورة فقط مع ترتيب العرض الإداري.
        return $query->where('is_published', true)->orderBy('sort_order', 'asc');
    }

    public function getImageUrlAttribute()
    {
        // تجهيز رابط الصورة الكامل للاستخدام المباشر في الواجهة.
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * العلاقة: خدمة واحدة تمتلك عدة مشاريع (One to Many).
     * ينعكس ذلك في الواجهة عند عرض المشاريع التابعة لكل خدمة.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * العلاقة: خدمة واحدة يمكن أن تمتلك عدة أخبار عبر علاقة متعددة الأشكال.
     * تستخدم لإظهار أخبار مرتبطة بخدمة معينة في الموقع.
     */
    public function news()
    {
        return $this->morphMany(News::class, 'newsable');
    }
}
