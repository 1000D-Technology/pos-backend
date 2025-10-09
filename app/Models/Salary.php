<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_month',
        'basic_salary',
        'allowances',
        'deductions',
        'total_salary',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total_salary' => 'decimal:2',
    ];

    protected $appends = ['total_paid', 'balance'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Get the total amount paid for this salary
     */
    protected function totalPaid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->payments()->sum('paid_amount') ?? 0
        );
    }

    /**
     * Get the remaining balance for this salary
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->total_salary - $this->total_paid
        );
    }
}
