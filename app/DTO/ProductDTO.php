<?php

namespace App\DTO;

class ProductDTO
{
    /**
     * List of attributes we accept and pass to the model.
     */
    public static array $fillable = [
        'name', 'type', 'category_id', 'unit_id', 'supplier_id',
        'mrp', 'locked_price', 'cabin_number', 'img', 'color', 'barcode',
    ];

    private array $attributes = [];

    private function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public static function fromArray(array $data): self
    {
        $attrs = [];

        // copy only fillable keys
        foreach (self::$fillable as $key) {
            if (array_key_exists($key, $data)) {
                $attrs[$key] = $data[$key];
            }
        }

        // normalize numeric strings to floats for prices
        if (isset($attrs['mrp']) && $attrs['mrp'] !== null) {
            $attrs['mrp'] = (float) $attrs['mrp'];
        }
        if (isset($attrs['locked_price']) && $attrs['locked_price'] !== null) {
            $attrs['locked_price'] = (float) $attrs['locked_price'];
        }

        // business rule: if NON_STOCKED then clear prices
        if (isset($attrs['type']) && $attrs['type'] === 'NON_STOCKED') {
            $attrs['mrp'] = null;
            $attrs['locked_price'] = null;
        }

        // ensure nullable fields exist and are null when not present
        foreach (['supplier_id', 'cabin_number', 'img', 'color', 'barcode'] as $nullable) {
            if (!array_key_exists($nullable, $attrs)) {
                $attrs[$nullable] = null;
            }
        }

        return new self($attrs);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
