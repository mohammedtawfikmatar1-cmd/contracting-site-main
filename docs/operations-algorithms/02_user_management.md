# خوارزمية إدارة المستخدمين

## الهدف

تمكين المشرف الأعلى من إنشاء وتحديث وحذف مستخدمي لوحة التحكم، وتحديد من يمتلك صلاحية إدارة المستخدمين عبر `is_super_admin`.

## الملفات المرتبطة

- `app/Http/Controllers/Admin/UserController.php`
- `app/Providers/AppServiceProvider.php`
- `routes/web.php`
- `app/Models/User.php`

## شرط الوصول

مسارات المستخدمين محمية بـ:

```php
Route::resource('users', AdminUserController::class)
    ->except(['show'])
    ->middleware('can:manage-users');
```

والصلاحية معرفة هكذا:

```php
Gate::define('manage-users', function (User $user): bool {
    return (bool) $user->is_super_admin;
});
```

## خوارزمية عرض المستخدمين

```mermaid
flowchart TD
    A["المشرف يفتح admin/users"] --> B["فحص auth و can:manage-users"]
    B --> C["جلب المستخدمين orderByDesc(id)"]
    C --> D["paginate(20)"]
    D --> E["عرض admin.users.index"]
```

## خوارزمية إنشاء مستخدم

```mermaid
flowchart TD
    A["POST admin/users"] --> B["validate name/email/password"]
    B --> C["فحص email unique"]
    C --> D["قراءة is_super_admin من checkbox"]
    D --> E["User::create"]
    E --> F["تحويل إلى قائمة المستخدمين مع رسالة نجاح"]
```

## خوارزمية تحديث مستخدم

```mermaid
flowchart TD
    A["PUT admin/users/{user}"] --> B["validate name/email/password اختياري"]
    B --> C["email unique مع تجاهل المستخدم الحالي"]
    C --> D{"هل password فارغة؟"}
    D -- "نعم" --> E["حذف password من البيانات حتى لا تتغير"]
    D -- "لا" --> F["إبقاء password للتحديث"]
    E --> G["تحديث is_super_admin"]
    F --> G
    G --> H["user->update"]
    H --> I["عودة للقائمة"]
```

## خوارزمية حذف مستخدم

```mermaid
flowchart TD
    A["DELETE admin/users/{user}"] --> B["فحص الصلاحية"]
    B --> C["user->delete"]
    C --> D["عودة للقائمة مع رسالة نجاح"]
```

## ملاحظات تشغيلية

- هذه العملية لا تغير الموقع العام مباشرة، لكنها تتحكم بمن يملك القدرة على تغيير الموقع.
- أول مستخدم ينشأ من شاشة setup يكون مشرفا أعلى تلقائيا.
- عند تحديث المستخدم، ترك كلمة المرور فارغة يعني الاحتفاظ بالكلمة القديمة.
