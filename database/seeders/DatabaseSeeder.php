<?php

/**
 * بذور قاعدة البيانات (Seeders)
 *
 * تُشغَّل يدويًا بـ: php artisan db:seed
 * تُستخدم لإنشاء بيانات أولية بعد migrations (مثل مستخدم تجريبي).
 * احذر: لا تشغّل على قاعدة إنتاج دون مراجعة ما سيُحذف أو يُستبدل.
 */
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
