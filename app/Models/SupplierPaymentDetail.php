<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentDetail extends Model
{
    //

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class);
    }
}
