<?php

namespace App\DTO;

use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerDTO
{
    public function __construct(
        public int $id,
        public ?string $name,
        public string $contact_no,
        public ?string $email,
        public ?string $address,
    ) {}

    /**
     * Create DTO from Customer model
     */
    public static function fromModel(Customer $customer): self
    {
        return new self(
            id: $customer->id,
            name: $customer->name,
            contact_no: $customer->contact_no,
            email: $customer->email,
            address: $customer->address,
        );
    }

    /**
     * Create DTO collection from multiple Customer models
     */
    public static function collection($customers): array
    {
        return $customers->map(function ($customer) {
            return self::fromModel($customer);
        })->toArray();
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_no' => $this->contact_no,
            'email' => $this->email,
            'address' => $this->address,
        ];
    }
}
