<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="Supplier",
 * title="Supplier",
 * description="Supplier data model",
 * @OA\Property(property="id", type="integer", format="int64", example=1),
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 * @OA\Property(property="phone", type="string", example="0771234567", nullable=true),
 * @OA\Property(property="address", type="string", example="123 Main St", nullable=true),
 * @OA\Property(property="company", type="string", example="Acme Inc"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="deleted_at", type="string", format="date-time", readOnly=true, nullable=true)
 * )
 */
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
    ];
}
