<?php

/**
 * الغرض من الملف:
 * تمثيل كيان الأخبار/المستجدات وربطه بكيانات مختلفة عبر علاقة متعددة الأشكال.
 *
 * التبعية:
 * App\Models\News ضمن نماذج Eloquent.
 *
 * المكونات الأساسية:
 * - HasTranslations للمحتوى متعدد اللغة.
 * - علاقة newsable polymorphic لربط الخبر بخدمة أو مشروع.
 *
 * خريطة تدفق البيانات:
 * - أخبار يدوية من لوحة التحكم (newsable اختياري).
 * - أخبار تلقائية تُنشأ عند نشر مشروع/مناقصة/وظيفة (newsable يشير إلى ذلك السجل).
 * ثم تُعرض في الصفحة الرئيسية وصفحة الأخبار وصفحة التفاصيل.
 */
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class News extends Model
{
    use HasFactory;
    use HasUniqueSlug;
    use HasTranslations;

    public array $translatable = [
        'title',
        'content',
        'category',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'newsable_type',
        'newsable_id',
        'title',
        'content',
        'image',
        'category',
        'is_published',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($news) {
            /*
             * published_at: إذا فعّل المسؤول "منشور" وترك التاريخ فارغًا، نضع الوقت الحالي
             * حتى تظهر الأخبار في scopePublished() (التي تتطلب published_at <= الآن).
             */
            if ($news->is_published && is_null($news->published_at)) {
                $news->published_at = now();
            }
        });
    }

    /**
     * Scope a query to only include published news.
     */
    public function scopePublished($query)
    {
        // عرض الأخبار المنشورة فقط والتي حان وقت نشرها.
        return $query->where('is_published', true)
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to filter news by category.
     */
    public function scopeInCategory($query, string $category)
    {
        // تصفية الأخبار حسب التصنيف.
        return $query->where('category', $category);
    }

    /**
     * Get the full URL for the image.
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        // إرجاع رابط الصورة عبر /media لتفادي مشاكل المسارات في بيئات التطوير.
        return $this->image ? route('media.file', ['path' => ltrim((string) $this->image, '/')]) : null;
    }

    /**
     * Get a short excerpt of the content.
     *
     * @param int $limit
     * @return string
     */
    public function getExcerpt(int $limit = 150)
    {
        // توليد ملخص مختصر مناسب لبطاقات الأخبار.
        return Str::limit(strip_tags($this->content), $limit);
    }

    /**
     * العلاقة: كل خبر ينتمي إلى كيان واحد متعدد الأشكال (Morph To).
     * قد يكون هذا الكيان خدمة أو مشروعًا بحسب تصميم لوحة التحكم.
     */
    public function newsable()
    {
        return $this->morphTo();
    }
}
