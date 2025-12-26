<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['mobile_number' => '+963991877688'],
            [
                'password' => Hash::make('admin123'),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'personal_photo' => 'users/photos/admin.jpg',
                'date_of_birth' => '1990-01-01',
                'id_photo' => 'users/id-photos/admin-id.jpg',
                'role' => 'admin',
                'status' => 'approved',
                'language_preference' => 'en',
                'balance' => 0.00,
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Mobile: +963991877688');
        $this->command->info('Password: admin123');
    }
}
