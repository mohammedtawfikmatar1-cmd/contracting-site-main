<?php

/**
 * الغرض من الملف:
 * Trait مشتركة لتوليد قيمة slug فريدة تلقائيا من عنوان العنصر.
 *
 * التبعية:
 * App\Models\Concerns\HasUniqueSlug وتُستخدم داخل عدة Models.
 *
 * المكونات الأساسية:
 * - الاستماع إلى حدث saving.
 * - استخراج العنوان حتى لو كان متعدد اللغات.
 * - توليد slug فريد مع معالجة العربية والإنجليزية.
 *
 * خريطة تدفق البيانات:
 * هذه الوحدة لا ترتبط مباشرة بلوحة التحكم، لكنها تخدم جميع النماذج
 * التي يُنشئها المدير من الإدارة وتحتاج إلى روابط صديقة للقراءة في الواجهة.
 */
namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            // في حال عدم وجود حقل title لا يمكن توليد slug.
            if (! isset($model->title)) {
                return;
            }

            // إذا لم يتغير العنوان وكان slug موجودا، فلا حاجة لإعادة التوليد.
            if (! $model->isDirty('title') && ! empty($model->slug)) {
                return;
            }

            $title = static::extractTitleForSlug($model);
            $model->slug = static::generateUniqueSlug($title, $model->getKey());
        });
    }

    protected static function extractTitleForSlug($model): string
    {
        // دعم الحقول المترجمة سواء كانت مصفوفة أو JSON مخزنا كنص.
        $title = $model->getAttribute('title');

        if (is_array($title)) {
            foreach (['ar', 'en'] as $locale) {
                $value = $title[$locale] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }

            foreach ($title as $value) {
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }

            return '';
        }

        if (is_string($title)) {
            $decoded = json_decode($title, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach (['ar', 'en'] as $locale) {
                    $value = $decoded[$locale] ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        return trim($value);
                    }
                }

                foreach ($decoded as $value) {
                    if (is_string($value) && trim($value) !== '') {
                        return trim($value);
                    }
                }
            }

            return trim($title);
        }

        return '';
    }

    protected static function generateUniqueSlug(string $title, $ignoreId = null): string
    {
        // إنشاء slug أساسي ثم إضافة عداد عند وجود تعارض.
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
            return 'item';
        }

        // الإبقاء على الأحرف العربية والإنجليزية والأرقام مع توحيد الفواصل.
        $slug = preg_replace('/[^A-Za-z0-9\x{0600}-\x{06FF}]+/u', '-', $title);
        $slug = trim((string) $slug, '-');

        if ($slug === '') {
            $slug = Str::slug($title);
        }

        return $slug !== '' ? $slug : 'item';
    }
}

