<?php

/**
 * الغرض من الملف:
 * تمثيل الصفحات التعريفية والثابتة التي تُدار من لوحة التحكم.
 *
 * التبعية:
 * App\Models\Page ضمن نماذج Eloquent.
 *
 * المكونات الأساسية:
 * - HasTranslations لدعم المحتوى متعدد اللغات.
 * - HasUniqueSlug لتوليد روابط صديقة للقراءة.
 *
 * خريطة تدفق البيانات:
 * يتم إنشاء الصفحات وتحديثها من قسم "الصفحات" في لوحة التحكم،
 * ثم تظهر في الواجهة الأمامية عبر روابط الصفحات الثابتة.
 */
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory;
    use HasUniqueSlug;
    use HasTranslations;

    public array $translatable = [
        'title',
        'content',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        // الاقتصار على الصفحات المسموح بعرضها للزوار.
        return $query->where('is_published', true);
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        // إنشاء رابط مباشر للصفحة اعتمادا على قيمة slug.
        return url('/pages/' . $this->slug);
    }

    /**
     * Find page by slug.
     *
     * @param string $slug
     * @return self|null
     */
    public static function findBySlug(string $slug)
    {
        // استخدام موحد للبحث عن الصفحة من المسار الأمامي.
        return self::where('slug', $slug)->first();
    }
}
