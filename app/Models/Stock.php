<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'qty',
        'max_retail_price',
        'cost_price',
        'expire_date',
        'qty_limit_alert',
    ];

    protected $casts = [
        'expire_date' => 'date',
        'qty' => 'decimal:4',
        'max_retail_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
