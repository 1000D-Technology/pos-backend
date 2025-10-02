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
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com")
 * )
 */
class UserDTO {
    public $id;
    public $name;
    public $email;

    public function __construct($id, $name, $email) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }
}