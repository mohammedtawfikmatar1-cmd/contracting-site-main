<?php

/**
 * =============================================================================
 * خدمة الأخبار التلقائية (NewsAutomationService)
 * =============================================================================
 *
 * ما الذي تفعله؟
 * --------------
 * عندما يحفظ المسؤول في لوحة التحكم: مشروعًا، أو مناقصة، أو وظيفة، يُطلق التطبيق
 * "حدثًا" (Event) ثم "مستمعًا" (Listener) يستدعي هذه الخدمة.
 *
 * النتيجة: إنشاء أو تحديث سجل في جدول `news` ليظهر الخبر في الموقع (الرئيسية،
 * صفحة الأخبار، إلخ) دون أن يضيف المسؤول الخبر يدويًا من قسم الأخبار.
 *
 * كيف نعرف أي خبر ينتمي لأي شيء؟
 * ---------------------------------
 * نستخدم علاقة Laravel اسمها Polymorphic (متعددة الأشكال):
 * - `newsable_type` = اسم صنف الموديل (مثل App\Models\Project)
 * - `newsable_id`   = رقم السجل في جدول ذلك الموديل
 *
 * إذا أُلغي نشر المشروع/المناقصة أو عُطّلت الوظيفة: نحذف الخبر المرتبط من هنا.
 *
 * ترتيب القراءة للمبتدئين:
 * ------------------------
 * 1) متحكمات الإدارة: ProjectController / TenderController / JobController (أسطر event(...))
 * 2) الأحداث: App\Events\*SavedForNews
 * 3) المستمعون: App\Listeners\SyncAutoNewsFrom*
 * 4) هذا الملف (المنطق الفعلي للحفظ في قاعدة البيانات)
 */
namespace App\Services;

use App\Models\Job;
use App\Models\News;
use App\Models\Project;
use App\Models\Service;
use App\Models\Tender;
use Illuminate\Support\Str;

class NewsAutomationService
{
    /**
     * مزامنة خبر مرتبط بخدمة منشورة.
     *
     * - إن كانت الخدمة غير منشورة: نحذف الخبر المرتبط إن وُجد.
     * - إن كانت منشورة: ننشئ/نحدّث خبرًا واحدًا لكل خدمة (مفتاح morph).
     */
    public function syncFromService(Service $service): void
    {
        $q = News::query()
            ->where('newsable_type', $service->getMorphClass())
            ->where('newsable_id', $service->id);

        if (! $service->is_published) {
            $q->delete();

            return;
        }

        $title = $this->plainString($service->title);
        $excerpt = Str::limit(strip_tags($this->plainString($service->description ?? '')), 400);
        $url = route('services.details', $service->slug);
        $content = $this->buildHtmlBody($excerpt, 'عرض تفاصيل الخدمة', $url);

        $news = News::query()->firstOrNew([
            'newsable_type' => $service->getMorphClass(),
            'newsable_id' => $service->id,
        ]);

        $news->title = ['ar' => $title ?: 'خدمة جديدة'];
        $news->content = ['ar' => $content];
        $news->category = ['ar' => 'خدمات'];
        $news->image = $service->image;
        $news->is_published = true;
        $news->published_at = $news->published_at ?? now();
        $news->save();
    }

    /**
     * مزامنة خبر مرتبط بمشروع منشور.
     *
     * - إن كان المشروع غير منشور: نحذف الخبر المرتبط إن وُجد.
     * - إن كان منشورًا: ننشئ أو نحدّث سجلًا واحدًا لكل مشروع (firstOrNew بالمفتاح morph).
     */
    public function syncFromProject(Project $project): void
    {
        // استعلام جاهز لحذف الخبر إذا لزم (نفس نوع الكيان + نفس المعرف)
        $q = News::query()
            ->where('newsable_type', $project->getMorphClass())
            ->where('newsable_id', $project->id);

        if (! $project->is_published) {
            $q->delete();

            return;
        }

        $title = $this->plainString($project->title);
        $excerpt = Str::limit(strip_tags($this->plainString($project->description ?? '')), 400);
        $url = route('projects.details', $project->slug);
        $content = $this->buildHtmlBody($excerpt, 'عرض تفاصيل المشروع', $url);

        // firstOrNew: إن وُجد خبر لهذا المشروع نحدّثه؛ وإلا ننشئ كائنًا جديدًا قبل الحفظ
        $news = News::query()->firstOrNew([
            'newsable_type' => $project->getMorphClass(),
            'newsable_id' => $project->id,
        ]);

        $news->title = ['ar' => $title ?: 'مشروع جديد'];
        $news->content = ['ar' => $content];
        $news->category = ['ar' => 'مشاريع'];
        $news->image = $project->image;
        $news->is_published = true;
        // نحافظ على أول تاريخ نشر؛ إن كان الخبر جديدًا نضع الوقت الحالي
        $news->published_at = $news->published_at ?? now();
        $news->save();
    }

    /**
     * مزامنة خبر مرتبط بمناقصة منشورة.
     * (لا صورة افتراضية في جدول المناقصات؛ صورة الخبر تبقى فارغة فتستخدم الواجهة صورة بديلة)
     */
    public function syncFromTender(Tender $tender): void
    {
        $q = News::query()
            ->where('newsable_type', $tender->getMorphClass())
            ->where('newsable_id', $tender->id);

        if (! $tender->is_published) {
            $q->delete();

            return;
        }

        $title = $this->plainString($tender->title);
        $excerpt = Str::limit(strip_tags($this->plainString($tender->description ?? '')), 400);
        // رابط يوجّه لصفحة المناقصات ثم يمرّر للبطاقة عبر المعرف (راجع tenders.blade.php)
        $url = route('tenders').'#tender-'.$tender->id;
        $content = $this->buildHtmlBody($excerpt, 'عرض المناقصة وتقديم العرض', $url);

        $news = News::query()->firstOrNew([
            'newsable_type' => $tender->getMorphClass(),
            'newsable_id' => $tender->id,
        ]);

        $news->title = ['ar' => $title ?: 'مناقصة جديدة'];
        $news->content = ['ar' => $content];
        $news->category = ['ar' => 'مناقصات'];
        $news->image = null;
        $news->is_published = true;
        $news->published_at = $news->published_at ?? now();
        $news->save();
    }

    /**
     * مزامنة خبر مرتبط بوظيفة مفعّلة.
     * الوظائف تستخدم الحقل `is_active` (وليس is_published).
     */
    public function syncFromJob(Job $job): void
    {
        $q = News::query()
            ->where('newsable_type', $job->getMorphClass())
            ->where('newsable_id', $job->id);

        if (! $job->is_active) {
            $q->delete();

            return;
        }

        $title = $this->plainString($job->title);
        $excerpt = Str::limit(strip_tags($this->plainString($job->description ?? '')), 400);
        $url = route('careers').'#job-'.$job->id;
        $content = $this->buildHtmlBody($excerpt, 'التقديم على الوظيفة', $url);

        $news = News::query()->firstOrNew([
            'newsable_type' => $job->getMorphClass(),
            'newsable_id' => $job->id,
        ]);

        $news->title = ['ar' => $title ?: 'وظيفة شاغرة'];
        $news->content = ['ar' => $content];
        $news->category = ['ar' => 'وظائف'];
        $news->image = null;
        $news->is_published = true;
        $news->published_at = $news->published_at ?? now();
        $news->save();
    }

    /**
     * استخراج نص عربي/إنجليزي من حقول قد تكون JSON ترجمة أو نصًا عاديًا.
     *
     * @param  mixed  $value  قد يكون نصًا أو JSON ترجمة (ar/en).
     */
    private function plainString(mixed $value): string
    {
        if (is_array($value)) {
            foreach (['ar', 'en'] as $loc) {
                if (! empty($value[$loc]) && is_string($value[$loc])) {
                    return trim($value[$loc]);
                }
            }

            foreach ($value as $v) {
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }

            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->plainString($decoded);
            }

            return trim($value);
        }

        return '';
    }

    /**
     * بناء HTML بسيط وآمن للعرض في صفحة الخبر (فقرات + رابط واحد).
     * دوال e() تحول المحارف الخاصة لتقليل مخاطر XSS في الروابط والنصوص.
     */
    private function buildHtmlBody(string $excerpt, string $linkLabel, string $url): string
    {
        $safeExcerpt = e($excerpt !== '' ? $excerpt : 'تم إضافة عنصر جديد؛ اطلع على التفاصيل من الرابط أدناه.');

        return '<p>'.$safeExcerpt.'</p>'
            .'<p><a href="'.e($url).'">'.e($linkLabel).'</a></p>';
    }
}
