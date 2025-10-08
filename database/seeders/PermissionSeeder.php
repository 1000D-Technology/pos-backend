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

            // Supplier management
            ['name' => 'Create Suppliers', 'slug' => 'suppliers.create', 'description' => 'Add new suppliers to the system.'],
            ['name' => 'Update Suppliers', 'slug' => 'suppliers.update', 'description' => 'Edit existing supplier details.'],
            ['name' => 'Delete Suppliers', 'slug' => 'suppliers.delete', 'description' => 'Remove suppliers from the system.'],
            ['name' => 'View Suppliers', 'slug' => 'suppliers.view', 'description' => 'View the list of suppliers.'],
            ['name' => 'Search Suppliers', 'slug' => 'suppliers.search', 'description' => 'Search suppliers in the system.'],



            // Unit Management
            ['name' => 'Manage Unit', 'slug' => 'unit.manage', 'description' => 'Create/update/delete.'],



            //Category Management
            ['name' => 'Manage Category', 'slug' => 'categories.manage', 'description' => 'Create/Update/Delete.'],


            // Customer Management
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'description' => 'Add new customers to the system.'],
            ['name' => 'Update Customers', 'slug' => 'customers.update', 'description' => 'Edit existing customer details.'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'description' => 'Remove customers from the system.'],
            ['name' => 'View Customers', 'slug' => 'customers.view', 'description' => 'View the list of customers.'],
            ['name' => 'Search Customers', 'slug' => 'customers.search', 'description' => 'Search customers in the system.'],
            ['name' => 'Restore Customers', 'slug' => 'customers.restore', 'description' => 'Restore deleted customers.'],

            // Bank management
            ['name' => 'Manage Bank Permission', 'slug' => 'bank.manage-permissions', 'description' => 'Assign or revoke permissions from bank.'],
            ['name' => 'View Bank', 'slug' => 'bank.view', 'description' => 'View the list of system bank.'],

            // Salary Management
            ['name' => 'Create Salary Slips', 'slug' => 'salaries.create', 'description' => 'Generate salary slips for employees.'],
            ['name' => 'View Salary Slips', 'slug' => 'salaries.view', 'description' => 'View salary slip records.'],
            ['name' => 'View Single Salary Slip', 'slug' => 'salaries.view', 'description' => 'View detailed salary slip information.'],
            ['name' => 'Pay Salaries', 'slug' => 'salaries.pay', 'description' => 'Record and manage salary payments.'],

        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
