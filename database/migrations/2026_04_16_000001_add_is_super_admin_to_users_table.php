<?php

/**
 * الغرض من الملف:
 * إضافة صلاحية "مشرف أعلى" داخل جدول المستخدمين.
 *
 * التبعية:
 * Migration تعديلي على جدول users.
 *
 * خريطة تدفق البيانات:
 * هذا الحقل يحدد مستوى الوصول في لوحة التحكم، ولا يُعرض مباشرة في الواجهة الأمامية،
 * لكنه يؤثر على من يملك صلاحية إدارة كامل محتوى الموقع.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('password'); // يحدد ما إذا كان المستخدم يمتلك أعلى الصلاحيات الإدارية.
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};

