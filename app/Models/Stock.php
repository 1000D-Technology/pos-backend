<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

/**
 * @OA\Schema(
 *   schema="Stock",
 *   title="Stock",
 *   description="Stock data model",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="product_id", type="integer"),
 *   @OA\Property(property="qty", type="number", format="float"),
 *   @OA\Property(property="max_retail_price", type="number", format="float", nullable=true),
 *   @OA\Property(property="cost_price", type="number", format="float", nullable=true),
 *   @OA\Property(property="expire_date", type="string", format="date", nullable=true),
 *   @OA\Property(property="qty_limit_alert", type="integer", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */

    protected $fillable = [
        'product_id',
        'qty',
        'max_retail_price',
        'cost_price',
        'manufacture_date',
        'expire_date',
        'max_retail_price',
        'cost_price',
        'cost_percentage',
        'cost_code',
        'profit_percentage',
        'profit',
        'discount_percentage',
        'discount',
        'whole_sale_price',
        'locked_price',
        'qty_limit_alert',
    ];

    protected $casts = [
        'expire_date' => 'date',
        'manufacture_date' => 'date',
        'qty' => 'decimal:4',
        'max_retail_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'profit' => 'decimal:2',
        'discount' => 'decimal:2',
        'whole_sale_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
