<?php

/**
 * الغرض من الملف:
 * تمثيل مستخدم لوحة التحكم وصلاحياته الأساسية.
 *
 * التبعية:
 * App\Models\User وهو النموذج المعتمد من Laravel للمصادقة.
 *
 * المكونات الأساسية:
 * - Notifiable لاستقبال الإشعارات داخل النظام.
 * - is_super_admin لتحديد أعلى مستوى من الصلاحيات.
 *
 * خريطة تدفق البيانات:
 * هذا النموذج يدير حسابات المشرفين داخل لوحة التحكم،
 * وأثره على الواجهة الأمامية غير مباشر من خلال إدارة المحتوى المنشور.
 */
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // تحويل الحقول إلى أنواع مناسبة عند القراءة والكتابة.
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }
}
