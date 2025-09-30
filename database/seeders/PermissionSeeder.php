<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Permissions...');
        
        $permissions = [
            // User Management
            ['name' => 'Manage User Permissions', 'slug' => 'users.manage-permissions', 'description' => 'Assign or revoke permissions from users.'],
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View the list of system users.'],

            // Product Management
            ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Add new products to the system.'],
            ['name' => 'Update Products', 'slug' => 'products.update', 'description' => 'Edit existing product details.'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'description' => 'Remove products from the system.'],
            ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'View the list of products.'],

          
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}