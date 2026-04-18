<?php

/**
 * الغرض من الملف:
 * إنشاء جدول المناقصات وربطها اختياريًا بالمشاريع.
 *
 * التبعية:
 * Migration لوحدة المناقصات في نظام المقاولات.
 *
 * المكونات الأساسية:
 * - project_id: ربط اختياري بمشروع.
 * - status و is_published: للتحكم في دورة حياة المناقصة.
 *
 * خريطة تدفق البيانات:
 * قسم "المناقصات" في لوحة التحكم يتحكم بما يُنشر في صفحة المناقصات بالواجهة.
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
        Schema::create("tenders", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للمناقصة.
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete(); // ربط اختياري بمشروع؛ عند حذف المشروع تصبح القيمة null.
            $table->string("title"); // عنوان المناقصة.
            $table->string("slug")->unique(); // رابط نصي فريد للمناقصة.
            $table->longText("description"); // تفاصيل نطاق العمل.
            $table->string("work_type")->nullable(); // نوع الأعمال المطلوبة.
            $table->string("location")->nullable(); // موقع تنفيذ الأعمال.
            $table->timestamp("closing_date"); // الموعد النهائي لاستقبال العروض.
            $table->enum("status", ["open", "closed", "completed"])->default("open"); // حالة المناقصة.
            $table->boolean("is_published")->default(false); // التحكم في ظهور المناقصة في الواجهة.
            $table->timestamps(); // وقت الإنشاء والتحديث.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("tenders");
    }
};
