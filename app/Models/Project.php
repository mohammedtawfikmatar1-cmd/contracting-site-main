<?php

/**
 * الغرض من الملف:
 * تمثيل كيان "المشروع" مع تفاصيل التنفيذ والصور والإنجازات.
 *
 * التبعية:
 * App\Models\Project ضمن طبقة النماذج في Laravel.
 *
 * المكونات الأساسية:
 * - علاقات مع الخدمات والعميل (اختياري) والأخبار والمناقصات.
 * - دعم الترجمة وتوليد slug تلقائي.
 *
 * خريطة تدفق البيانات:
 * قسم "المشاريع" في لوحة التحكم يحدد بيانات المشروع وحالة نشره،
 * ثم تظهر المشاريع المنشورة في واجهة الموقع ضمن صفحات المشاريع وتفاصيل كل مشروع.
 */
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasFactory;
    use HasUniqueSlug;
    use HasTranslations;

    public array $translatable = [
        'title',
        'description',
        'category',
        'location',
        'area',
        'status_text',
        'challenges',
        'solutions',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'service_id',
        'client_id',
        'description',
        'image',
        'category',
        'location',
        'area',              // مساحة المشروع
        'status_text',       // حالة المشروع (مكتمل، قيد التنفيذ)
        'completion_date',   // تاريخ الإنجاز
        'challenges',        // التحديات (نص طويل)
        'solutions',         // الحلول (نص طويل)
        'achievements',      // الإنجازات (JSON قائمة)
        'gallery',           // معرض الصور (JSON قائمة روابط)
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'achievements' => 'array',
        'gallery' => 'array',
        'completion_date' => 'date',
    ];

    /**
     * Scope a query to only include published projects.
     */
    public function scopePublished($query)
    {
        // اعتماد المشاريع المنشورة فقط للعرض في الواجهة الأمامية.
        return $query->where('is_published', true);
    }

    /**
     * Get the full URL for the main image.
     */
    public function getImageUrlAttribute()
    {
        // إرجاع المسار الكامل لصورة المشروع لتسهيل العرض في Blade.
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * العلاقة: المشروع ينتمي إلى خدمة واحدة (Many to One).
     * هذا الربط يسمح بتجميع المشاريع حسب الخدمة في الواجهة.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * العلاقة: المشروع قد ينتمي إلى عميل (Many to One) أو يبقى بدون عميل.
     * يُستخدم لعرض شعار العميل ضمن أقسام العملاء في الواجهة.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * العلاقة: المشروع يملك أخبارًا متعددة عبر علاقة متعددة الأشكال.
     * تستخدم لعرض مستجدات مرتبطة بمشروع بعينه.
     */
    public function news()
    {
        return $this->morphMany(News::class, 'newsable');
    }

    /**
     * العلاقة: المشروع قد يحتوي عدة مناقصات (One to Many).
     * بيانات المناقصات تُدار من لوحة التحكم وتنعكس في صفحة المناقصات.
     */
    public function tenders()
    {
        return $this->hasMany(Tender::class);
    }
}
