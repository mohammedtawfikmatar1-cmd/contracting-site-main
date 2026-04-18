<?php

/**
 * الغرض من الملف:
 * إنشاء جداول المستخدمين الافتراضية في Laravel (users، tokens reset، sessions).
 *
 * التبعية:
 * Migration أساسي للمصادقة وإدارة الجلسات.
 *
 * خريطة تدفق البيانات:
 * هذه البيانات تخدم تسجيل الدخول إلى لوحة التحكم، بينما أثرها على الواجهة
 * يكون غير مباشر عبر إدارة المحتوى من قبل المستخدمين المخولين.
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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // المعرف الفريد للمستخدم.
            $table->string('name'); // اسم المستخدم.
            $table->string('email')->unique(); // بريد إلكتروني فريد لتسجيل الدخول.
            $table->timestamp('email_verified_at')->nullable(); // وقت توثيق البريد الإلكتروني.
            $table->string('password'); // كلمة المرور المشفرة.
            $table->rememberToken(); // رمز "تذكرني" للجلسات الطويلة.
            $table->timestamps(); // تتبع تاريخ الإنشاء والتحديث.
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // البريد المستخدم في طلب إعادة التعيين.
            $table->string('token'); // رمز إعادة تعيين كلمة المرور.
            $table->timestamp('created_at')->nullable(); // وقت إنشاء طلب الاستعادة.
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // معرف الجلسة.
            $table->foreignId('user_id')->nullable()->index(); // المستخدم المرتبط بالجلسة (إن وجد).
            $table->string('ip_address', 45)->nullable(); // عنوان IP للعميل.
            $table->text('user_agent')->nullable(); // معلومات المتصفح/الجهاز.
            $table->longText('payload'); // بيانات الجلسة المخزنة.
            $table->integer('last_activity')->index(); // آخر نشاط (لتنظيف الجلسات المنتهية).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
