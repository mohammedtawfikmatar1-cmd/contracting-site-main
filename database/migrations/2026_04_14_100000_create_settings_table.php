<?php

/**
 * الغرض من الملف:
 * إنشاء جدول الإعدادات العامة للموقع (نصوص، صور، ألوان، قيم JSON).
 *
 * التبعية:
 * Migration في Laravel لإدارة بيانات الإعدادات الديناميكية.
 *
 * المكونات الأساسية:
 * key/value مع type لتحديد طريقة معالجة القيمة في لوحة التحكم.
 *
 * خريطة تدفق البيانات:
 * يتم تعديل هذه القيم من قسم "الإعدادات" في لوحة التحكم،
 * ثم تستخدمها صفحات الواجهة مثل الهيدر، الفوتر، وبيانات التواصل.
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
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); // المعرف الفريد لكل إعداد.
            $table->string('key')->unique(); // مفتاح الإعداد (فريد) للاستخدام البرمجي.
            $table->text('value')->nullable(); // قيمة الإعداد الفعلية.
            $table->string('type')->default('text'); // نوع القيمة لتحديد طريقة الإدخال والعرض (نص/صورة/لون/JSON).
            $table->string('description')->nullable(); // وصف داخلي لمساعدة مدير النظام.
            $table->timestamps(); // تتبع تاريخ الإنشاء والتحديث.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
