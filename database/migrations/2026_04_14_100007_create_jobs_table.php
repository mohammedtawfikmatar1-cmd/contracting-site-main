<?php

/**
 * الغرض من الملف:
 * إنشاء جدول الوظائف الشاغرة المنشورة في بوابة التوظيف بالموقع.
 *
 * التبعية:
 * Migration مخصص لوحدة الوظائف.
 *
 * خريطة تدفق البيانات:
 * تُدار الوظائف من قسم "الوظائف" في لوحة التحكم،
 * وتظهر للزوار في صفحة التوظيف مع تفاصيل كل فرصة.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("job_posts", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للوظيفة.
            $table->string("title"); // عنوان الوظيفة.
            $table->string("slug")->unique(); // رابط نصي فريد لتفاصيل الوظيفة.
            $table->string("location")->nullable(); // موقع العمل.
            $table->string("type")->nullable(); // نوع الدوام (كامل/جزئي...).
            $table->string("experience")->nullable(); // الحد الأدنى للخبرة.
            $table->string("qualification")->nullable(); // المؤهل المطلوب.
            $table->text("description")->nullable(); // وصف المهام الوظيفية.
            $table->json("requirements")->nullable(); // متطلبات الوظيفة بصيغة JSON.
            $table->json("skills")->nullable(); // المهارات المطلوبة بصيغة JSON.
            $table->date("closing_date")->nullable(); // تاريخ إغلاق التقديم.
            $table->boolean("is_active")->default(true); // حالة تفعيل ظهور الوظيفة للزوار.
            $table->timestamps(); // وقت الإنشاء والتحديث.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("job_posts");
    }
};
