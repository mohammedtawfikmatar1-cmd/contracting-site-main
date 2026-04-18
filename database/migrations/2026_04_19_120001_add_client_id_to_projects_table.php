<?php

/**
 * الغرض من الملف:
 * ربط المشاريع اختياريا بعميل (شريك/جهة منفذة معها المشروع).
 *
 * التبعية:
 * Migration يعدّل جدول projects.
 *
 * خريطة تدفق البيانات:
 * يُحدد client_id من نماذج المشاريع أو من شاشة العميل عند اختيار مشاريع مرتبطة،
 * ويُستخدم لاحقا في عرض شعارات العملاء والربط المنطقي في الواجهة.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // عميل اختياري: قد يكون المشروع عاما دون ربط بعميل محدد.
            $table->foreignId('client_id')
                ->nullable()
                ->after('service_id')
                ->constrained('clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
