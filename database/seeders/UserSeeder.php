<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Users...');

        // Get all permissions and key them by slug for easy lookup
        $permissions = Permission::all()->keyBy('slug');

        // 1. Create the Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'nic' => '123456789V',
                'basic_salary' => 75000.00,
                'contact_no' => '+1234567890',
                'address' => '123 Admin Street, City',
            ]
        );
        // Assign all permissions to the admin
        $admin->permissions()->sync($permissions->pluck('id'));
        $this->command->info('Admin user created/updated.');


        // 2. Create the Cashier User
        $cashier = User::updateOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Cashier User',
                'password' => Hash::make('password'),
                'nic' => '987654321V',
                'basic_salary' => 45000.00,
                'contact_no' => '+0987654321',
                'address' => '456 Cashier Avenue, Town',
            ]
        );
        $this->command->info('Cashier user created/updated.');


        // 3. Create a User with NO permissions for testing
        User::updateOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('password'),
                'nic' => '555555555V',
                'basic_salary' => 30000.00,
                'contact_no' => '+5555555555',
                'address' => '789 Guest Lane, Village',
            ]
        );
        $this->command->info('Guest user created/updated.');
    }
}
