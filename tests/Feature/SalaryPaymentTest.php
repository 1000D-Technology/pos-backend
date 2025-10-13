<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SalaryPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_salary_payment_requires_permission(): void
    {
        $user = User::factory()->create();
        $payingUser = User::factory()->create();

        // Create a salary for a user
        $salary = Salary::create([
            'user_id' => $user->id,
            'salary_month' => now()->format('Y-m'),
            'basic_salary' => 50000,
            'allowances' => 5000,
            'deductions' => 2000,
            'total_salary' => 53000,
        ]);

        Sanctum::actingAs($payingUser);

        // Without permission should be forbidden
        $response = $this->postJson('/api/salary-payments', [
            'salary_id' => $salary->id,
            'salary_paid_by' => $payingUser->id,
            'paid_amount' => 53000,
        ]);

        $response->assertForbidden();
    }

    public function test_create_salary_payment_success(): void
    {
        $user = User::factory()->create();
        $payingUser = User::factory()->create();

        $salary = Salary::create([
            'user_id' => $user->id,
            'salary_month' => now()->format('Y-m'),
            'basic_salary' => 50000,
            'allowances' => 5000,
            'deductions' => 2000,
            'total_salary' => 53000,
        ]);

        // give permission to payingUser
        $permission = Permission::create(['name' => 'Pay Salaries', 'slug' => 'salaries.pay']);
        $payingUser->permissions()->attach($permission);

        Sanctum::actingAs($payingUser);

        $response = $this->postJson('/api/salary-payments', [
            'salary_id' => $salary->id,
            'salary_paid_by' => $payingUser->id,
            'paid_amount' => 53000,
            'payment_method' => 'bank',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('salary_payments', [
            'salary_id' => $salary->id,
            'salary_paid_by' => $payingUser->id,
            'paid_amount' => 53000.00,
        ]);
    }
}
