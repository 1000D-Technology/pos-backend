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
     * 
     * @param \Illuminate\Support\Collection<int, Customer>|array<Customer> $customers
     * @return array<int, self>
     */
    public static function collection(Collection|array $customers): array
    {
        if (is_array($customers)) {
            return array_map(fn($customer) => $customer->only(['id', 'name', 'contact_no', 'email', 'address']), $customers);
        }

        return $customers->map(function ($customer) {
            return $customer->only(['id', 'name', 'contact_no', 'email', 'address']);
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
