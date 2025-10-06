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
        Schema::create('company_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company')->onDelete('cascade');
            $table->foreignId('bank_id')->constrained('bank')->onDelete('cascade');
            $table->string('acc_no',50)->unique();
            $table->string('branch',100);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'bank_id', 'acc_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_bank');
    }
};
