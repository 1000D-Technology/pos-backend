<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('salary_month'); // Format: YYYY-MM
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('total_salary', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure unique salary per user per month
            $table->unique(['user_id', 'salary_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
