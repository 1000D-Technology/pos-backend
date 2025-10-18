<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="Invoice",
 * title="Invoice",
 * description="Invoice master record",
 * @OA\Property(property="id", type="integer", readOnly=true, example=1),
 * @OA\Property(property="customer_id", type="integer", description="Foreign key to Customer"),
 * @OA\Property(property="user_id", type="integer", description="Foreign key to User (Creator)"),
 * @OA\Property(property="total_amount", type="number", format="float", description="Total gross amount before any discounts", example=1450.00),
 * @OA\Property(property="discount", type="number", format="float", description="Total discount applied (item discounts + header discount)", example=60.00),
 * @OA\Property(property="tax", type="number", format="float", description="Calculated tax amount", example=139.00),
 * @OA\Property(property="grand_total", type="number", format="float", description="Final total (Taxable Amount + Tax)", example=1529.00),
 * @OA\Property(property="balance", type="number", format="float", description="Outstanding balance (Grand Total - Total Paid)", example=126.50),
 * @OA\Property(property="status", type="string", enum={"pending", "partial_paid", "paid"}, description="Payment status", example="partial_paid"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time"),
 * @OA\Property(property="customer", ref="#/components/schemas/Customer"),
 * @OA\Property(property="payments", type="array", @OA\Items(ref="#/components/schemas/InvoicePayment")),
 * @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/InvoiceItem"))
 * )
 */
class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'user_id',
        'total_amount',
        'discount',
        'balance',
        'tax',
        'status',
        'grand_total',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'discount' => 'float',
        'balance' => 'float',
        'tax' => 'float',
        'grand_total' => 'float',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function payments(){
        return $this->hasMany(InvoicePayment::class);
    }

    public function items(){
        return $this->hasMany(InvoiceItem::class);
    }
}
