<?php

/**
 * الغرض من الملف:
 * تمثيل عميل (شريك/جهة) مرتبط بمشروع واحد أو أكثر، مع شعار ووصف.
 *
 * التبعية:
 * App\Models\Client ضمن Eloquent ORM.
 *
 * المكونات الأساسية:
 * - علاقة hasMany مع المشاريع عبر client_id في جدول projects.
 * - توليد slug فريد من اسم العميل.
 *
 * خريطة تدفق البيانات:
 * يُنشأ ويُحدّث من لوحة التحكم (قسم العملاء)،
 * ويظهر في الصفحة الرئيسية ضمن شريط الشعارات،
 * وفي صفحة "عملاؤنا" عند تفعيل الإعداد clients_page_enabled.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Client $client) {
            if ($client->isDirty('name') || blank($client->slug)) {
                $client->slug = static::generateUniqueSlugFromName((string) $client->name, $client->getKey());
            }
        });
    }

    /**
     * العلاقة: عميل واحد يملك عدة مشاريع (One to Many عبر client_id).
     * يُشترط في الإدارة ربط عميل بمشروع واحد على الأقل عند الحفظ.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        // توحيد عرض شعارات العملاء عبر مسار الوسائط المركزي.
        return $this->logo ? route('media.file', ['path' => ltrim((string) $this->logo, '/')]) : null;
    }

    /**
     * توليد slug فريد من الاسم مع دعم العربية مشابه لمنطق HasUniqueSlug.
     */
    protected static function generateUniqueSlugFromName(string $title, $ignoreId = null): string
    {
        $base = static::slugify($title);
        $candidate = $base;
        $counter = 1;

        while (
            static::query()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $candidate = $base.'-'.$counter++;
        }

        return $candidate;
    }

    protected static function slugify(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return 'client';
        }

        $slug = preg_replace('/[^A-Za-z0-9\x{0600}-\x{06FF}]+/u', '-', $title);
        $slug = trim((string) $slug, '-');

        if ($slug === '') {
            $slug = Str::slug($title) ?: 'client';
        }

        return $slug;
    }
}
