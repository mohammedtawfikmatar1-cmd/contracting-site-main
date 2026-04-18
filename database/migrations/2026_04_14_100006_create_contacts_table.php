<?php

/**
 * الغرض من الملف:
 * إنشاء جدول طلبات التواصل الواردة من النماذج الأمامية (استفسار/خدمة/وظيفة).
 *
 * التبعية:
 * Migration لإدارة بيانات الرسائل الواردة داخل لوحة التحكم.
 *
 * خريطة تدفق البيانات:
 * الزائر يرسل الطلب من الواجهة الأمامية، ثم يظهر في قسم "طلبات التواصل"
 * داخل الإدارة لمتابعة الحالة والتعامل معه.
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
        Schema::create("contacts", function (Blueprint $table) {
            $table->id(); // المعرف الفريد للطلب.
            $table->string("full_name"); // اسم مقدم الطلب.
            $table->string("phone"); // رقم الهاتف للتواصل.
            $table->string("email"); // البريد الإلكتروني.
            $table->string("request_type"); // نوع الطلب: عام/خدمة/وظيفة.
            $table->string("service_requested")->nullable(); // الخدمة المطلوبة عند اختيار نوع خدمة.
            $table->string("cv_file")->nullable(); // ملف السيرة الذاتية في حالة طلب التوظيف.
            $table->longText("message"); // نص الرسالة.
            $table->enum("status", ["pending", "in_progress", "completed"])->default("pending"); // حالة معالجة الطلب داخل الإدارة.
            $table->timestamps(); // وقت الإرسال وآخر تحديث.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("contacts");
    }
};
