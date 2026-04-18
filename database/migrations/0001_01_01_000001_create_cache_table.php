<?php

/**
 * الغرض من الملف:
 * إنشاء جداول الكاش وقفل الكاش عند استخدام سائق قاعدة البيانات.
 *
 * التبعية:
 * Migration بنيوي لخدمات الأداء في Laravel.
 *
 * خريطة تدفق البيانات:
 * لا يُدار مباشرة من لوحة التحكم، لكنه يدعم سرعة استرجاع البيانات
 * التي تظهر في الموقع ولوحة الإدارة.
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
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary(); // مفتاح الكاش الفريد.
            $table->mediumText('value'); // القيمة المخزنة في الكاش.
            $table->integer('expiration')->index(); // وقت انتهاء الصلاحية لتحسين التنظيف.
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary(); // مفتاح القفل.
            $table->string('owner'); // معرف الجهة المالكة للقفل.
            $table->integer('expiration')->index(); // وقت انتهاء القفل.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
