<?php

/**
 * الغرض من الملف:
 * إنشاء جدول العملاء (شعارات وشركاء) المرتبطين بمشاريع الشركة.
 *
 * التبعية:
 * Migration ضمن Laravel Schema.
 *
 * المكونات الأساسية:
 * - حقول التعريف والوصف والشعار والنشر والترتيب.
 *
 * خريطة تدفق البيانات:
 * تُدار البيانات من قسم "العملاء" في لوحة التحكم،
 * وتُعرض في الصفحة الرئيسية وصفحة "عملاؤنا" عند التفعيل.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // المعرف الفريد للعميل.
            $table->string('name'); // اسم العميل كما يظهر في الموقع.
            $table->string('slug')->unique(); // رابط نصي لصفحة تفاصيل العميل (إن لزم).
            $table->longText('description')->nullable(); // وصف مختصر أو تفصيلي عن العميل.
            $table->string('logo')->nullable(); // مسار شعار العميل داخل التخزين العام.
            $table->unsignedInteger('sort_order')->default(0); // ترتيب الظهور في القوائم والشريط المتحرك.
            $table->boolean('is_published')->default(true); // يتحكم في إظهار العميل للزوار.
            $table->timestamps(); // أوقات الإنشاء والتحديث.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
