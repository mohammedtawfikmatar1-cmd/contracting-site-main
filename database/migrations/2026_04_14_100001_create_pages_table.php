<?php

/**
 * الغرض من الملف:
 * إنشاء جدول الصفحات الثابتة/التعريفية (مثل: من نحن، سياسة الخصوصية).
 *
 * التبعية:
 * Migration ضمن قاعدة بيانات Laravel.
 *
 * المكونات الأساسية:
 * slug فريد لتوليد روابط الصفحات، مع حالة نشر للتحكم في الظهور.
 *
 * خريطة تدفق البيانات:
 * قسم "الصفحات" في لوحة التحكم يغذي الصفحات التعريفية في الموقع الأمامي.
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
        Schema::create("pages", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للصفحة.
            $table->string("title"); // عنوان الصفحة.
            $table->string("slug")->unique(); // رابط نصي فريد للصفحة.
            $table->longText("content")->nullable(); // المحتوى النصي/HTML للصفحة.
            $table->string("template")->default("default"); // القالب المستخدم لعرض الصفحة.
            $table->boolean("is_published")->default(false); // حالة نشر الصفحة في الواجهة.
            $table->timestamps(); // وقت الإنشاء والتحديث.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("pages");
    }
};
