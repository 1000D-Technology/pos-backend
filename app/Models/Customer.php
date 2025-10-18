<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="Customer",
 * title="Customer",
 * description="Customer data model for POS system",
 * @OA\Property(property="id", type="integer", readOnly=true, example=1, description="Unique ID of the customer"),
 * @OA\Property(property="name", type="string", example="Wimal Perera", description="Full name of the customer"),
 * @OA\Property(property="contact_no", type="string", example="0771234567", nullable=true, description="Customer's phone number"),
 * @OA\Property(property="email", type="string", format="email", example="wimal@example.com", nullable=true, description="Customer's email address"),
 * @OA\Property(property="address", type="string", example="Galle Road, Colombo 4", nullable=true, description="Customer's physical address"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_no',
        'email',
        'address',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function invoices(){
        return $this->hasMany(Invoice::class);
    }
}
