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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nic')->nullable()->after('email');
            $table->decimal('basic_salary', 10, 2)->nullable()->after('nic');
            $table->string('contact_no')->nullable()->after('basic_salary');
            $table->text('address')->nullable()->after('contact_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nic', 'basic_salary', 'contact_no', 'address']);
        });
    }
};
