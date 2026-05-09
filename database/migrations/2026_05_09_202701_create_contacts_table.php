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
    public function up(): void
    {
        Schema::create("contacts", function (Blueprint $table) {
            $table->id();
            // الربط مع جدول العملاء
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            
            $table->string("request_type"); 
            $table->string("service_requested")->nullable(); 
            $table->longText("message"); 
            $table->enum("status", ["pending", "in_progress", "completed"])->default("pending"); 
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("contacts");
    }
};