<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="InvoiceItem",
 * title="Invoice Item",
 * description="Details of an item sold in an invoice",
 * @OA\Property(property="id", type="integer", readOnly=true, example=1),
 * @OA\Property(property="invoice_id", type="integer"),
 * @OA\Property(property="stock_id", type="integer", description="Reference to the Stock item sold"),
 * @OA\Property(property="sold_price", type="number", format="float", description="Unit price at the time of sale", example=500.00),
 * @OA\Property(property="qty", type="integer", description="Quantity sold", example=2),
 * @OA\Property(property="discount", type="number", format="float", description="Item-level discount amount", example=50.00),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class InvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'stock_id',
        'sold_price',
        'qty',
        'discount',
    ];

    protected $casts = [
        'sold_price' => 'float',
        'discount' => 'float',
        'qty' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
