<?php

/**
 * الغرض من الملف:
 * إنشاء جدول الإشعارات الداخلي المعتمد من Laravel Notifications.
 *
 * التبعية:
 * Migration لدعم نظام الإشعارات للمستخدمين الإداريين.
 *
 * خريطة تدفق البيانات:
 * عند وصول طلبات جديدة (مثل طلبات التواصل)، تُرسل إشعارات إلى الإدارة،
 * وتظهر في قسم الإشعارات بلوحة التحكم.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // معرف UUID فريد للإشعار.
            $table->string('type'); // نوع كلاس الإشعار المرسل.
            $table->morphs('notifiable'); // الكيان المستلم للإشعار (غالبا المستخدم الإداري).
            $table->text('data'); // بيانات الإشعار بصيغة JSON.
            $table->timestamp('read_at')->nullable(); // وقت قراءة الإشعار.
            $table->timestamps(); // وقت إنشاء الإشعار وتحديثه.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
