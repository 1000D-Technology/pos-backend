<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\DTO\ApiResponse;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permission middleware will enforce permissions
    }

    public function rules(): array
    {
        return [
            'qty' => 'nullable|numeric|min:0',
            'max_retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'expire_date' => 'nullable|date',
            'qty_limit_alert' => 'nullable|integer|min:0',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $response = ApiResponse::error('Validation failed', $errors)->toArray();
        throw new HttpResponseException(response()->json($response, 422));
    }
}
