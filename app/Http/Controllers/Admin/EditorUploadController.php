<?php

/**
 * الغرض من الملف:
 * استقبال رفع وسائط محرر النصوص (Summernote) عبر AJAX وإرجاع رابط مباشر (URL).
 *
 * لماذا؟
 * حفظ الصور أو الفيديو كـ Base64 داخل قاعدة البيانات يسبب تضخم حجم البيانات وبطء ملحوظ مع الوقت.
 * الحل الاحترافي هو رفع الوسيط للتخزين ثم حفظ الرابط فقط داخل المحتوى.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EditorImageUploadRequest;
use App\Services\EditorImageCleaner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorUploadController extends Controller
{
    /**
     * رفع صورة أو فيديو وإرجاع رابطها العام ليتم إدراجها داخل محتوى الـ Editor.
     */
    public function store(EditorImageUploadRequest $request): JsonResponse
    {
        $context = $request->validated('context') ?? 'general';
        $type = $request->hasFile('video') ? 'video' : 'image';
        $file = $request->file($type);

        // لكل محرر/قسم مجلد مستقل داخل public disk لسهولة الإدارة والتنظيف لاحقا.
        $path = $file->store("editor/{$context}", 'public' );

        return response()->json([
            // نستخدم مسار /media الموحد حتى يعمل العرض في جميع البيئات (مجلدات فرعية/بدون ضبط APP_URL).
            'url' => route('media.file', ['path' => $path]),
            'path' => $path,
            'type' => $type,
            'mime' => $file->getMimeType(),
        ]);
    }

    /**
     * حذف وسيط رُفع من المحرر ولم يعد مستخدما داخل النص.
     */
    public function destroy(Request $request, EditorImageCleaner $editorImages): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['nullable', 'string', 'max:2048'],
            'path' => ['nullable', 'string', 'max:2048'],
        ]);

        $deleted = $editorImages->deleteImageByReference($validated['path'] ?? null)
            || $editorImages->deleteImageByReference($validated['url'] ?? null);

        return response()->json(['deleted' => $deleted]);
    }
}
