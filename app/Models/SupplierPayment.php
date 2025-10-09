<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="SupplierPayment",
 * title="Supplier Payment (Bill)",
 * description="The main record for a supplier bill or invoice.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="supplier_id", type="integer", example=5, description="ID of the associated supplier."),
 * @OA\Property(property="total", type="number", format="float", example=10000.00, description="Total bill amount."),
 * @OA\Property(property="due_amount", type="number", format="float", example=5000.00, description="Remaining amount due."),
 * @OA\Property(property="bill_img", type="string", nullable=true, example="http://app/storage/supplier_bills/bill.jpg", description="URL to the uploaded bill image."),
 * @OA\Property(property="status", type="string", enum={"unpaid", "partial paid", "paid"}, example="partial paid", description="Payment status of the bill."),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class SupplierPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'supplier_id',
        'total',
        'due_amount',
        'bill_img',
        'status',
    ];
    protected $casts = [
        'total' => 'float',
        'due_amount' => 'float',
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
    public function details(){
        return $this->hasMany(SupplierPaymentDetail::class);
    }
}
