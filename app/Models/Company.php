<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="Company",
 * title="Company",
 * description="Company model for business entities.",
 * @OA\Property(property="id", type="integer", format="int64", example=1),
 * @OA\Property(property="name", type="string", example="Tech Solutions Ltd"),
 * @OA\Property(property="email", type="string", format="email", example="contact@techsolutions.com"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, readOnly=true)
 * )
 */
class Company extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'company';

    protected $fillable = [
        'name',
        'email',
    ];

    public function banks()
    {
        return $this->belongsToMany(Bank::class, 'company_bank')
            ->withPivot(['id', 'acc_no', 'branch','deleted_at'])
            ->wherePivotNull('deleted_at');
    }
}
