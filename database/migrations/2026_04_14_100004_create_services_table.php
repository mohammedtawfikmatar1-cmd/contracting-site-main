<?php

/**
 * الغرض من الملف:
 * إنشاء جدول الخدمات الذي يمثل المجالات الرئيسية التي تقدمها شركة المقاولات.
 *
 * التبعية:
 * ملف Migration ضمن دورة بناء قاعدة البيانات في Laravel.
 *
 * المكونات الأساسية:
 * Schema و Blueprint لإنشاء الأعمدة وفرض القيود.
 *
 * خريطة تدفق البيانات:
 * تُدار الخدمات من قسم "الخدمات" في لوحة التحكم، وتُعرض في واجهة الموقع،
 * كما ترتبط بها المشاريع عبر service_id.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("services", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للخدمة.
            $table->string("title"); // اسم الخدمة المعروض للزوار.
            $table->string("slug")->unique(); // رابط فريد للخدمة في المسارات.
            $table->longText("description"); // وصف شامل للخدمة.
            $table->string("image")->nullable(); // صورة الخدمة الرئيسية.
            $table->string("icon")->nullable(); // أيقونة الخدمة (اختيارية).
            $table->text("overview")->nullable(); // نبذة مختصرة لعرض سريع في الواجهة.
            $table->json("achievements")->nullable(); // قائمة إنجازات الخدمة بصيغة JSON.
            $table->integer("sort_order")->default(0); // ترتيب الظهور في صفحات الخدمات.
            $table->boolean("is_published")->default(false); // حالة النشر في الواجهة الأمامية.
            $table->timestamps(); // تاريخا الإنشاء والتحديث.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("services");
    }
};
