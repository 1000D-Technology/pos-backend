<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\DTO\ApiResponse;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust with auth/permission middleware
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:STOCKED,NON_STOCKED',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
                'mrp' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) {
                        if ($this->input('type') === 'NON_STOCKED' && !is_null($value)) {
                            $fail('MRP must be null for NON_STOCKED products.');
                        }
                    },
                ],
                'locked_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) {
                        if ($this->input('type') === 'NON_STOCKED' && !is_null($value)) {
                            $fail('Locked price must be null for NON_STOCKED products.');
                        }
                    },
                ],
            'cabin_number' => 'nullable|string|max:100',
            'img' => 'nullable|url',
            'color' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
        ];
    }

    protected function prepareForValidation(): void
    {
        // prepare validation if needed
    }

    /**
     * Return a JSON response on validation failure to ensure API clients get JSON errors
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $response = ApiResponse::error('Validation failed', $errors)->toArray();
        throw new HttpResponseException(response()->json($response, 422));
    }
}
