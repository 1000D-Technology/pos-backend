<?php

namespace App\DTO;

/**
 * @OA\Schema(
 *     schema="UserDTO",
 *     type="object",
 *     title="User DTO",
 *     description="User Data Transfer Object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="nic", type="string", example="123456789V"),
 *     @OA\Property(property="basic_salary", type="number", format="float", example=50000.00),
 *     @OA\Property(property="contact_no", type="string", example="+1234567890"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City")
 * )
 */
class UserDTO {
    public $id;
    public $name;
    public $email;
    public $nic;
    public $basic_salary;
    public $contact_no;
    public $address;

    public function __construct($id, $name, $email, $nic = null, $basic_salary = null, $contact_no = null, $address = null) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->nic = $nic;
        $this->basic_salary = $basic_salary;
        $this->contact_no = $contact_no;
        $this->address = $address;
    }
}