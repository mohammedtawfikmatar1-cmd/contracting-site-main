<?php

/**
 * ترحيل (Migration): جعل أعمدة العلاقة المتعددة الأشكال في جدول news اختيارية.
 *
 * لماذا؟
 * ------
 * - الأخبار التي يكتبها المسؤول يدويًا من قسم الأخبار لا تحتاج ربطًا بمشروع أو غيره.
 * - الأخبار التلقائية تملأ newsable_type و newsable_id لتشير إلى المشروع/المناقصة/الوظيفة.
 *
 * ملاحظة: يعتمد تنفيذ ALTER على MySQL في بيئة التطوير الحالية؛ بيئات أخرى قد تحتاج تعديلًا منفصلًا.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['newsable_type', 'newsable_id']);
        });

        DB::statement('ALTER TABLE `news` MODIFY `newsable_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `news` MODIFY `newsable_type` VARCHAR(255) NULL');

        Schema::table('news', function (Blueprint $table) {
            $table->index(['newsable_type', 'newsable_id']);
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('news')->whereNull('newsable_id')->orWhereNull('newsable_type')->delete();

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['newsable_type', 'newsable_id']);
        });

        DB::statement('ALTER TABLE `news` MODIFY `newsable_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `news` MODIFY `newsable_type` VARCHAR(255) NOT NULL');

        Schema::table('news', function (Blueprint $table) {
            $table->index(['newsable_type', 'newsable_id']);
        });
    }
};
