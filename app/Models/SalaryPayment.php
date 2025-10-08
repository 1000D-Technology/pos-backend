<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $table = 'salary_payments';

    protected $fillable = [
        'salary_id',
        'salary_paid_by',
        'payment_method',
        'paid_amount',
        'payment_date',
        'payment_note',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function salary(): BelongsTo
    {
        return $this->belongsTo(Salary::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salary_paid_by');
    }
}
