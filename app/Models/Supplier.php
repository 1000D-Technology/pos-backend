<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="Supplier",
 * title="Supplier Model",
 * description="Supplier data model",
 * @OA\Property(property="id", type="integer", readOnly=true, example=1),
 * @OA\Property(property="name", type="string", example="ABC Trade Co."),
 * @OA\Property(property="email", type="string", format="email", example="abc@trade.com"),
 * @OA\Property(property="phone", type="string", example="0711234567", nullable=true),
 * @OA\Property(property="address", type="string", example="15 Main Road, Colombo", nullable=true),
 * @OA\Property(property="company", type="string", example="ABC Group"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true, example="2023-10-26T10:00:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true, example="2023-10-26T10:00:00.000000Z"),
 * )
 */
class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
    ];
}
