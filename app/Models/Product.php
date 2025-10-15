<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
/**
 * @OA\Schema(
 *   schema="Product",
 *   title="Product",
 *   description="Product data model",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Sample Product"),
 *   @OA\Property(property="type", type="string", example="STOCKED"),
 *   @OA\Property(property="category_id", type="integer"),
 *   @OA\Property(property="unit_id", type="integer"),
 *   @OA\Property(property="supplier_id", type="integer", nullable=true),
 *   @OA\Property(property="mrp", type="number", format="float", nullable=true),
 *   @OA\Property(property="locked_price", type="number", format="float", nullable=true),
 *   @OA\Property(property="barcode", type="string", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'unit_id',
        'supplier_id',
        'mrp',
        'locked_price',
        'cabin_number',
        'img',
        'color',
        'barcode',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'locked_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
