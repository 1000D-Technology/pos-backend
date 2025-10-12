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

    public static function fromArray(array $data, bool $isUpdate = false): self
    {
        $attrs = [];

    // copy only fillable keys that exist in the input
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

    // business rule: if NON_STOCKED then clear prices (only when type is explicitly present)
        if (array_key_exists('type', $attrs) && $attrs['type'] === 'NON_STOCKED') {
            $attrs['mrp'] = null;
            $attrs['locked_price'] = null;
        }

    // For create (not update) ensure nullable fields exist and are null when not present
        if (!$isUpdate) {
            foreach (['supplier_id', 'cabin_number', 'img', 'color', 'barcode'] as $nullable) {
                if (!array_key_exists($nullable, $attrs)) {
                    $attrs[$nullable] = null;
                }
            }
        }

        return new self($attrs);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
