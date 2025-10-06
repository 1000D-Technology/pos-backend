<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="Bank",
 * title="Bank",
 * description="Bank model for financial institutions.",
 * @OA\Property(property="id", type="integer", format="int64", example=1),
 * @OA\Property(property="name", type="string", example="People's Bank"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, readOnly=true)
 * )
 */
class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank';
    protected $fillable = [
        'name'
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_bank')
            ->withPivot(['id', 'acc_no', 'branch','delete_at'])
            ->wherePivotNull('deleted_at');
    }
}
