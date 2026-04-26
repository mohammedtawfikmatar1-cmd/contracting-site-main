<?php

/**
 * الغرض من الملف:
 * تمثيل الإعدادات العامة القابلة للتعديل ديناميكيا من لوحة التحكم.
 *
 * التبعية:
 * App\Models\Setting ضمن طبقة Eloquent.
 *
 * المكونات الأساسية:
 * - getValue / setValue للوصول الموحّد إلى الإعدادات.
 * - parseValue لتحويل القيمة حسب نوعها المخزن.
 *
 * خريطة تدفق البيانات:
 * أي تعديل في قسم "الإعدادات" داخل الإدارة يُخزن هنا،
 * ثم يُستهلك مباشرة في الواجهة مثل الشعار، وسائل التواصل، والألوان أو النصوص العامة.
 *
 * للمبتدئين: كل صف في جدول settings = زوج (key, value, type). نوع الحقل يحدد كيف يُفسَّر value (نص، لون، JSON، صورة...).
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'string',
        'type' => 'string',
    ];

    /**
     * Get setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        // حماية إضافية أثناء التثبيت أو قبل تنفيذ الـ migrations.
        if (! Schema::hasTable((new self())->getTable())) {
            return $default;
        }

        // تحسين الأداء: كاش للإعدادات الأكثر استخداماً في الواجهة.
        // مدة قصيرة كافية لتسريع الموقع وتجنب بقاء بيانات قديمة فترة طويلة.
        $cacheKey = "settings:value:{$key}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($key, $default) {
            $setting = self::query()->where('key', $key)->first();
            if (! $setting) {
                return $default;
            }

            return $setting->parseValue();
        });
    }

    /**
     * Set setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @return self
     */
    public static function setValue(string $key, $value, ?string $type = 'text')
    {
        // تخزين المصفوفات كنص JSON للحفاظ على بنية البيانات.
        $storedValue = is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $value;

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
            ]
        );

        // تفريغ الكاش لهذا المفتاح + تفريغ كاش تجميعة إعدادات الموقع إن وُجد.
        self::clearCacheForKey($key);

        return $setting;
    }

    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            self::clearCacheForKey($setting->key);
        });

        static::deleted(function (self $setting) {
            self::clearCacheForKey($setting->key);
        });
    }

    private static function clearCacheForKey(string $key): void
    {
        try {
            Cache::forget("settings:value:{$key}");
            Cache::forget('site:settings:all');
        } catch (\Throwable $e) {
            // في حال عدم توفر كاش فعّال، لا نكسر سير العمل.
        }
    }

    /**
     * Parse the value based on the type.
     *
     * @return mixed
     */
    public function parseValue()
    {
        // تحويل القيمة الخام إلى نوع مناسب للاستهلاك في الكود أو الواجهة.
        switch ($this->type) {
            case 'json':
                $decoded = json_decode((string) $this->value, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->value;
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            case 'integer':
                return (int) $this->value;
            case 'image':
                // توحيد رابط الوسائط عبر مسار /media لضمان عمل الصور حتى بدون storage:link.
                return $this->value ? route('media.file', ['path' => ltrim((string) $this->value, '/')]) : null;
            default:
                return $this->value;
        }
    }

    /**
     * Scope a query to only include settings of a certain type.
     */
    public function scopeOfType($query, string $type)
    {
        // تسهيل استرجاع الإعدادات بحسب النوع عند بناء نماذج الإدارة.
        return $query->where('type', $type);
    }
}
