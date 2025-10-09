<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
