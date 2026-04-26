<?php

/**
 * الغرض من الملف:
 * استقبال رفع صور محرر النصوص (Summernote) عبر AJAX وإرجاع رابط مباشر (URL).
 *
 * لماذا؟
 * حفظ الصور كـ Base64 داخل قاعدة البيانات يسبب تضخم حجم البيانات وبطء ملحوظ مع الوقت.
 * الحل الاحترافي هو رفع الصورة للتخزين ثم حفظ الرابط فقط داخل المحتوى.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EditorImageUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EditorUploadController extends Controller
{
    /**
     * رفع صورة وإرجاع رابطها العام ليتم إدراجها داخل محتوى الـ Editor.
     */
    public function store(EditorImageUploadRequest $request): JsonResponse
    {
        $context = $request->validated('context') ?? 'general';

        // لكل محرر/قسم مجلد مستقل داخل public disk لسهولة الإدارة والتنظيف لاحقا.
        $path = $request->file('image')->store("editor/{$context}", 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
        ]);
    }
}

