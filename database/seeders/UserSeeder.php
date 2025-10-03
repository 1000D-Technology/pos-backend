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
            ]
        );
        // Assign specific permissions to the cashier
        $cashierPermissions = [
         
            $permissions['products.view']->id,
        ];
        $cashier->permissions()->sync($cashierPermissions);
        $this->command->info('Cashier user created/updated.');


        // 3. Create a User with NO permissions for testing
        User::updateOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('password'),
            ]
        );
        $this->command->info('Guest user created/updated.');
    }
}