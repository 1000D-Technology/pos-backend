<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'unit_id',
        'supplier_id',
        'mrp',
        'locked_price',
        'cabin_number',
        'img',
        'color',
        'barcode',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'locked_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
