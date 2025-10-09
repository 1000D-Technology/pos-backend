<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
