<?php

/**
 * الغرض من الملف:
 * إنشاء جداول طوابير المهام (Queue) وسجل المهام الفاشلة.
 *
 * التبعية:
 * Migration بنيوي لخدمات المعالجة الخلفية في Laravel.
 *
 * خريطة تدفق البيانات:
 * لا يرتبط مباشرة بواجهة الإدارة، لكنه يدعم تنفيذ العمليات غير المتزامنة
 * مثل الإشعارات والمعالجات الثقيلة التي تنعكس نتائجها في لوحة التحكم والموقع.
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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id(); // المعرف الفريد للوظيفة في الطابور.
            $table->string('queue')->index(); // اسم الطابور (فهرس لتسريع السحب).
            $table->longText('payload'); // حمولة المهمة (البيانات المراد تنفيذها).
            $table->unsignedTinyInteger('attempts'); // عدد محاولات التنفيذ.
            $table->unsignedInteger('reserved_at')->nullable(); // وقت حجز الوظيفة من العامل.
            $table->unsignedInteger('available_at'); // وقت السماح بتنفيذ الوظيفة.
            $table->unsignedInteger('created_at'); // وقت إضافة الوظيفة للطابور.
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // معرف الدفعة.
            $table->string('name'); // اسم الدفعة.
            $table->integer('total_jobs'); // العدد الكلي للمهام ضمن الدفعة.
            $table->integer('pending_jobs'); // المهام التي لم تنته بعد.
            $table->integer('failed_jobs'); // عدد المهام الفاشلة.
            $table->longText('failed_job_ids'); // معرفات المهام الفاشلة.
            $table->mediumText('options')->nullable(); // خيارات الدفعة.
            $table->integer('cancelled_at')->nullable(); // وقت الإلغاء إن تم.
            $table->integer('created_at'); // وقت إنشاء الدفعة.
            $table->integer('finished_at')->nullable(); // وقت اكتمال الدفعة.
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // المعرف الفريد لسجل الفشل.
            $table->string('uuid')->unique(); // UUID فريد للمهمة الفاشلة.
            $table->text('connection'); // اتصال الطابور المستخدم.
            $table->text('queue'); // اسم الطابور.
            $table->longText('payload'); // بيانات المهمة.
            $table->longText('exception'); // تفاصيل الاستثناء.
            $table->timestamp('failed_at')->useCurrent(); // وقت حدوث الفشل.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
