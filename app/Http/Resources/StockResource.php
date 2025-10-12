<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'qty_limit_alert' => $this->qty_limit_alert,
            'max_retail_price' => $this->max_retail_price,
            'cost_price' => $this->cost_price,
            'expire_date' => $this->when($this->expire_date, $this->expire_date?->toDateString()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
