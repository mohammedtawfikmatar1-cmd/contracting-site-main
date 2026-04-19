<?php

/**
 * إنشاء/تحديث/حذف أخبار تلقائية مرتبطة بمشروع أو مناقصة أو وظيفة منشورة.
 * يُستدعى من مستمعي الأحداث بعد الحفظ في لوحة التحكم.
 */
namespace App\Services;

use App\Models\Job;
use App\Models\News;
use App\Models\Project;
use App\Models\Tender;
use Illuminate\Support\Str;

class NewsAutomationService
{
    public function syncFromProject(Project $project): void
    {
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

        $news = News::query()->firstOrNew([
            'newsable_type' => $project->getMorphClass(),
            'newsable_id' => $project->id,
        ]);

        $news->title = ['ar' => $title ?: 'مشروع جديد'];
        $news->content = ['ar' => $content];
        $news->category = ['ar' => 'مشاريع'];
        $news->image = $project->image;
        $news->is_published = true;
        $news->published_at = $news->published_at ?? now();
        $news->save();
    }

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

    private function buildHtmlBody(string $excerpt, string $linkLabel, string $url): string
    {
        $safeExcerpt = e($excerpt !== '' ? $excerpt : 'تم إضافة عنصر جديد؛ اطلع على التفاصيل من الرابط أدناه.');

        return '<p>'.$safeExcerpt.'</p>'
            .'<p><a href="'.e($url).'">'.e($linkLabel).'</a></p>';
    }
}
