<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="SupplierPaymentDetail",
 * title="Supplier Payment Detail",
 * description="Individual payment transaction against a supplier bill.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="supplier_payment_id", type="integer", example=10, description="Foreign key to the parent SupplierPayment bill."),
 * @OA\Property(property="paid_amount", type="number", format="float", example=5000.00),
 * @OA\Property(property="payment_method", type="string", enum={"Cash", "Bank Transfer", "Cheque", "Credit Card"}, example="Bank Transfer"),
 * @OA\Property(property="note", type="string", nullable=true, example="Reference: TRF123"),
 * @OA\Property(property="img", type="string", nullable=true, example="http://app/storage/payment_proofs/proof_1.jpg", description="URL to the payment receipt image."),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class SupplierPaymentDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_payment_id',
        'paid_amount',
        'payment_method',
        'note',
        'img',
    ];

    protected $casts = [
        'paid_amount' => 'float',
    ];

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class);
    }
}
