<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->date('manufacture_date')->nullable()->after('product_id');
            $table->decimal('cost_percentage', 8, 2)->nullable()->after('manufacture_date');
            $table->string('cost_code')->nullable()->after('cost_percentage');
            $table->decimal('profit_percentage', 8, 2)->nullable()->after('cost_code');
            $table->decimal('profit', 14, 2)->nullable()->after('profit_percentage');
            $table->decimal('discount_percentage', 8, 2)->nullable()->after('profit');
            $table->decimal('discount', 14, 2)->nullable()->after('discount_percentage');
            $table->decimal('whole_sale_price', 14, 2)->nullable()->after('discount');
            $table->decimal('locked_price', 14, 2)->nullable()->after('whole_sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn([
                'manufacture_date', 'cost_percentage', 'cost_code', 'profit_percentage', 'profit',
                'discount_percentage', 'discount', 'whole_sale_price', 'locked_price'
            ]);
        });
    }
};
