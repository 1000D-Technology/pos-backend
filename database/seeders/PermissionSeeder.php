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

            // Stock Management
            ['name' => 'Create Stocks', 'slug' => 'stocks.create', 'description' => 'Create new stock entries (used by GRN/Invoices or manual API).'],
            ['name' => 'View Stocks', 'slug' => 'stocks.view', 'description' => 'View stock levels and details.'],
            ['name' => 'Update Stocks', 'slug' => 'stocks.update', 'description' => 'Update stock pricing, quantities and expiry details.'],
            ['name' => 'Search Stocks', 'slug' => 'stocks.search', 'description' => 'Search or filter stock items (e.g., low stock).'],

            // Unit Management
            ['name' => 'Manage Unit', 'slug' => 'units.manage', 'description' => 'Create/update/delete.'],



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

            // Company management
            ['name' => 'Company Permission', 'slug' => 'company.manage-permissions', 'description' => 'Assign or revoke permissions from company.'],
            ['name' => 'View company', 'slug' => 'company.view', 'description' => 'View the list of system company.'],
            // Salary Management
            ['name' => 'Create Salary Slips', 'slug' => 'salaries.create', 'description' => 'Generate salary slips for employees.'],
            ['name' => 'View Salary Slips', 'slug' => 'salaries.view', 'description' => 'View salary slip records.'],
            ['name' => 'Update Salary Slips', 'slug' => 'salaries.update', 'description' => 'Edit existing salary slip details.'],
            ['name' => 'Delete Salary Slips', 'slug' => 'salaries.delete', 'description' => 'Remove salary slips from the system.'],

            // Salary Payment Management
            ['name' => 'Create Salary Payments', 'slug' => 'salary-payments.create', 'description' => 'Record new salary payments (regular, advance, bonus, etc).'],
            ['name' => 'View Salary Payments', 'slug' => 'salary-payments.view', 'description' => 'View salary payment records and history.'],
            ['name' => 'Update Salary Payments', 'slug' => 'salary-payments.update', 'description' => 'Edit existing salary payment details.'],
            ['name' => 'Delete Salary Payments', 'slug' => 'salary-payments.delete', 'description' => 'Remove salary payment records from the system.'],
            // Staff Management
            ['name' => 'Manage Staff', 'slug' => 'staff-roles.manage', 'description' => 'Create/Update/Delete staff roles.'],
            // Company management
            ['name' => 'Company Permission', 'slug' => 'company.manage-permissions', 'description' => 'Assign or revoke permissions from company.'],
            ['name' => 'View company', 'slug' => 'company.view', 'description' => 'View the list of system company.'],

            // Supplier payment management
            ['name' => 'Suppliers Payments Permission', 'slug' => 'suppliers.manage-permissions', 'description' => 'Add new suppliers payment to the system.'],
            ['name' => 'View Suppliers Payments', 'slug' => 'suppliers-payments.view', 'description' => 'Add new suppliers payment to the system.'],
            // Company bank account management
            ['name' => 'Manage Company Bank Accounts', 'slug' => 'company-bank.manage', 'description' => 'Create/Update/Delete company bank accounts.'],
            ['name' => 'View Company Bank Accounts', 'slug' => 'company-bank.view', 'description' => 'View the list of company bank accounts.'],
            
            // Attendance management
            ['name' => 'Manage Attendances', 'slug' => 'attendances.manage', 'description' => 'Create/Update/Delete/View attendances.'],

        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
