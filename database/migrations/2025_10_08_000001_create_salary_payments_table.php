<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->constrained('salaries')->onDelete('cascade');
            $table->foreignId('salary_paid_by')->constrained('users')->onDelete('cascade');
            $table->enum('payment_type', ['regular', 'advance', 'bonus', 'overtime', 'commission', 'allowance', 'adjustment'])->default('regular');
            $table->string('payment_method')->nullable();
            $table->decimal('paid_amount', 10, 2);
            $table->date('payment_date')->nullable();
            $table->text('payment_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
