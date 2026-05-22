<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class EditorImageCleaner
{
    /**
     * Delete editor media that existed in old content but no longer exist in new content.
     */
    public function deleteRemovedImages(mixed $oldContent, mixed $newContent): int
    {
        $oldPaths = $this->extractImagePaths($oldContent);
        $newPaths = $this->extractImagePaths($newContent);

        return $this->deletePaths(array_diff($oldPaths, $newPaths));
    }

    /**
     * Delete every editor media file referenced by a content field before deleting its owner.
     */
    public function deleteImagesFromContent(mixed $content): int
    {
        return $this->deletePaths($this->extractImagePaths($content));
    }

    public function deleteImageByReference(?string $reference): bool
    {
        $path = $this->pathFromReference($reference);

        if (! $path) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * @return array<int, string>
     */
    public function extractImagePaths(mixed $content): array
    {
        $paths = [];

        foreach ($this->stringsFromContent($content) as $html) {
            foreach ($this->mediaReferencesFromHtml($html) as $reference) {
                $path = $this->pathFromReference($reference);

                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * @param iterable<int, string> $paths
     */
    private function deletePaths(iterable $paths): int
    {
        $deleted = 0;

        foreach (array_values(array_unique(is_array($paths) ? $paths : iterator_to_array($paths))) as $path) {
            if (Storage::disk('public')->delete($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * @return array<int, string>
     */
    private function stringsFromContent(mixed $content): array
    {
        if ($content === null) {
            return [];
        }

        if (is_array($content)) {
            $strings = [];

            foreach ($content as $value) {
                array_push($strings, ...$this->stringsFromContent($value));
            }

            return $strings;
        }

        if (! is_string($content)) {
            return [];
        }

        $trimmed = trim($content);

        if ($trimmed !== '' && in_array($trimmed[0], ['{', '['], true)) {
            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->stringsFromContent($decoded);
            }
        }

        return [$content];
    }

    /**
     * @return array<int, string>
     */
    private function mediaReferencesFromHtml(string $html): array
    {
        if ($html === '') {
            return [];
        }

        preg_match_all('/<(?:img|video|source)\b[^>]*>/i', $html, $mediaTags);

        $references = [];

        foreach ($mediaTags[0] ?? [] as $tag) {
            foreach (['src', 'data-editor-upload-path'] as $attribute) {
                if (preg_match('/\b'.preg_quote($attribute, '/').'\s*=\s*([\'"])(.*?)\1/i', $tag, $match)) {
                    $references[] = $match[2];
                }
            }
        }

        return $references;
    }

    private function pathFromReference(?string $reference): ?string
    {
        if (! is_string($reference) || trim($reference) === '') {
            return null;
        }

        $reference = html_entity_decode(trim($reference), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (str_starts_with($reference, 'blob:') || str_starts_with($reference, 'data:')) {
            return null;
        }

        $path = parse_url($reference, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : $reference;
        $path = str_replace('\\', '/', rawurldecode($path));
        $path = preg_replace('#/+#', '/', $path) ?: '';
        $path = ltrim($path, '/');

        $mediaPosition = strpos($path, 'media/');
        if ($mediaPosition !== false) {
            $path = substr($path, $mediaPosition + strlen('media/'));
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $path = ltrim($path, '/');

        if (! str_starts_with($path, 'editor/') || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }
}
