<?php

/**
 * الغرض من الملف:
 * إنشاء جدول الأخبار/المستجدات المستخدمة لعرض الإعلانات والمحتوى الإخباري.
 *
 * التبعية:
 * Migration ضمن قاعدة بيانات Laravel.
 *
 * المكونات الأساسية:
 * - newsable polymorphic: لربط الخبر بكيانات مختلفة عند الحاجة.
 * - حقول النشر: للتحكم في إظهار الخبر وتوقيت نشره.
 *
 * خريطة تدفق البيانات:
 * قسم "الأخبار" في لوحة التحكم يغذي صفحة الأخبار في الموقع.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("news", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للخبر.
            $table->morphs('newsable'); // علاقة متعددة الأشكال لربط الخبر بأي كيان داعم للأخبار.
            $table->string("title"); // عنوان الخبر.
            $table->string("slug")->unique(); // رابط نصي فريد لتفاصيل الخبر.
            $table->longText("content"); // محتوى الخبر الكامل.
            $table->string("image")->nullable(); // صورة مرافقة للخبر.
            $table->string("category")->nullable(); // تصنيف الخبر (اختياري).
            $table->boolean("is_published")->default(false); // حالة الظهور في واجهة الموقع.
            $table->timestamp("published_at")->nullable(); // وقت النشر الفعلي.
            $table->timestamps(); // تتبع وقت الإنشاء والتحديث.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("news");
    }
};
