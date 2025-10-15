<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                ];
            }),
            'supplier' => $this->whenLoaded('supplier', function () {
                return $this->supplier ? ['id' => $this->supplier->id, 'name' => $this->supplier->name] : null;
            }),
            'mrp' => $this->mrp,
            'locked_price' => $this->locked_price,
            'cabin_number' => $this->cabin_number,
            'img' => $this->img,
            'color' => $this->color,
            'barcode' => $this->barcode,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
