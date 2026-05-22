<?php

namespace Tests\Feature;

use App\Services\EditorImageCleaner;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorImageCleanerTest extends TestCase
{
    public function test_it_deletes_only_removed_editor_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('editor/news/removed.jpg', 'image');
        Storage::disk('public')->put('editor/news/kept.jpg', 'image');
        Storage::disk('public')->put('news/main.jpg', 'image');

        $oldContent = '<p><img src="/media/editor/news/removed.jpg"><img src="http://example.test/site/media/editor/news/kept.jpg"><img src="/media/news/main.jpg"></p>';
        $newContent = '<p><img src="/media/editor/news/kept.jpg"></p>';

        app(EditorImageCleaner::class)->deleteRemovedImages($oldContent, $newContent);

        Storage::disk('public')->assertMissing('editor/news/removed.jpg');
        Storage::disk('public')->assertExists('editor/news/kept.jpg');
        Storage::disk('public')->assertExists('news/main.jpg');
    }

    public function test_it_reads_editor_images_from_translated_json_content(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('editor/pages/ar.jpg', 'image');
        Storage::disk('public')->put('editor/pages/en.jpg', 'image');

        $content = json_encode([
            'ar' => '<p><img src="/media/editor/pages/ar.jpg"></p>',
            'en' => '<p><img src="/media/editor/pages/en.jpg"></p>',
        ], JSON_UNESCAPED_SLASHES);

        app(EditorImageCleaner::class)->deleteImagesFromContent($content);

        Storage::disk('public')->assertMissing('editor/pages/ar.jpg');
        Storage::disk('public')->assertMissing('editor/pages/en.jpg');
    }

    public function test_it_deletes_removed_editor_videos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('editor/pages/removed.mp4', 'video');
        Storage::disk('public')->put('editor/pages/kept.mp4', 'video');

        $oldContent = '<video controls data-editor-upload-path="/media/editor/pages/removed.mp4"><source src="/media/editor/pages/removed.mp4" type="video/mp4"></video>'
            . '<video controls data-editor-upload-path="/media/editor/pages/kept.mp4"><source src="/media/editor/pages/kept.mp4" type="video/mp4"></video>';
        $newContent = '<video controls data-editor-upload-path="/media/editor/pages/kept.mp4"><source src="/media/editor/pages/kept.mp4" type="video/mp4"></video>';

        app(EditorImageCleaner::class)->deleteRemovedImages($oldContent, $newContent);

        Storage::disk('public')->assertMissing('editor/pages/removed.mp4');
        Storage::disk('public')->assertExists('editor/pages/kept.mp4');
    }
}
