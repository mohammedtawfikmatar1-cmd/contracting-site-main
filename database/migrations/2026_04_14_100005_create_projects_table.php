<?php

/**
 * الغرض من الملف:
 * إنشاء جدول المشاريع الذي يمثل الأعمال المنفذة أو الجارية داخل النظام.
 *
 * التبعية (Namespace Concept):
 * هذا الملف جزء من طبقة ترحيل قاعدة البيانات في Laravel ضمن مجلد database/migrations.
 *
 * المكونات الأساسية المستخدمة:
 * - Schema: لإنشاء الجدول وإدارته.
 * - Blueprint: لتعريف الأعمدة والقيود.
 *
 * خريطة تدفق البيانات:
 * بيانات هذا الجدول تُدار من قسم "المشاريع" في لوحة التحكم (ProjectController)،
 * وتنعكس في صفحات المشاريع بالواجهة الأمامية مع إمكانية التصفية حسب الخدمة.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("projects", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للمشروع.
            $table->foreignId('service_id')->constrained()->cascadeOnDelete(); // ربط المشروع بخدمة؛ حذف الخدمة يحذف مشاريعها تلقائيا.
            $table->string("title"); // عنوان المشروع المعروض في الموقع ولوحة التحكم.
            $table->string("slug")->unique(); // رابط نصي فريد لاستخدامه في مسار تفاصيل المشروع.
            $table->longText("description"); // وصف تفصيلي للمشروع.
            $table->string("image")->nullable(); // الصورة الرئيسية للمشروع.
            $table->string("category")->nullable(); // تصنيف المشروع (اختياري).
            $table->string("location")->nullable(); // موقع تنفيذ المشروع.
            $table->string("area")->nullable(); // مساحة المشروع أو نطاقه (اختياري).
            $table->string("status_text")->nullable(); // نص الحالة الظاهر للزوار (منجز/قيد التنفيذ...).
            $table->date("completion_date")->nullable(); // تاريخ الإنجاز المتوقع أو الفعلي.
            $table->text("challenges")->nullable(); // أبرز التحديات أثناء التنفيذ.
            $table->text("solutions")->nullable(); // حلول التحديات المعروضة ضمن القصة التنفيذية.
            $table->json("achievements")->nullable(); // إنجازات المشروع بصيغة JSON لعرضها كقائمة ديناميكية.
            $table->json("gallery")->nullable(); // معرض صور المشروع بصيغة JSON.
            $table->boolean("is_published")->default(false); // يتحكم في إظهار المشروع بالواجهة الأمامية.
            $table->timestamps(); // وقت الإنشاء والتحديث لتتبع التعديلات.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("projects");
    }
};
