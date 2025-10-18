<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('user_id')->constrained('users')->comment('The user who created the invoice');

            $table->decimal('total_amount', 10, 2)->comment('Sum of all item prices before discounts/taxes');
            $table->decimal('discount', 10, 2)->default(0.00)->comment('Total discount applied to the invoice');
            $table->decimal('tax', 10, 2)->default(0.00)->comment('Total calculated tax amount');
            $table->decimal('grand_total', 10, 2)->comment('Final amount payable (Total - Discount + Tax)');
            $table->decimal('balance', 10, 2)->comment('Outstanding balance remaining (Grand Total - Paid Amount)');

            $table->enum('status', ['pending', 'paid', 'cancelled', 'partial_paid'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
