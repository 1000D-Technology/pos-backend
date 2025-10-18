<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 * schema="InvoicePayment",
 * title="Invoice Payment",
 * description="Details of a payment received for an invoice",
 * @OA\Property(property="id", type="integer", readOnly=true, example=1),
 * @OA\Property(property="invoice_id", type="integer"),
 * @OA\Property(property="payment_method", type="string", enum={"Cash", "Credit Card", "Bank Transfer", "Cheque"}, example="Cash"),
 * @OA\Property(property="total_given_amount", type="number", format="float", description="The amount tendered by the customer for this payment", example=1400.00),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_method',
        'total_given_amount',
    ];

    protected $casts = [
        'total_given_amount' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
