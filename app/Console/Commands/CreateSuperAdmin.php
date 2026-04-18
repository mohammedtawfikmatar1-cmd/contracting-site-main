<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
  protected $signature = 'app:create-super-admin 
                        {name} 
                        {email} 
                        {password}';

    protected $description = 'Create a super admin user';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // تحقق من عدم تكرار الإيميل
        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists!');
            return;
        }

        // إنشاء المستخدم
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_super_admin' => true,
        ]);

        $this->info('Super Admin created successfully!');
    }
}