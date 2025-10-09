<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('product');

        return [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:STOCKED,NON_STOCKED',
            'category_id' => 'sometimes|required|exists:categories,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'mrp' => 'nullable|numeric|min:0',
            'locked_price' => 'nullable|numeric|min:0',
            'cabin_number' => 'nullable|string|max:100',
            'img' => 'nullable|url',
            'color' => 'nullable|string|max:50',
            'barcode' => "nullable|string|max:255|unique:products,barcode,{$id}",
        ];
    }
}
