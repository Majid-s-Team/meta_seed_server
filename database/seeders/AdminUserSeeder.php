<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create an admin user for livestream and app management.
     * Uses ADMIN_EMAIL and ADMIN_PASSWORD from .env, or defaults.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');

        if (User::where('email', $email)->exists()) {
            $this->command->info("Admin user already exists: {$email}");
            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->command->info("Admin user created: {$email}");
    }
}
