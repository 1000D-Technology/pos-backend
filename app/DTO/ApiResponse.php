<?php

namespace App\DTO;

class ApiResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public mixed $data = null,
        public ?array $errors = null,
    ){}

    public static function success(string $message, $data = null):self
    {
        return new self(
            success: true,
            message: $message,
            data: $data
        );
    }

    public static function error(string $message, ?array $errors = null):self
    {
        return new self(
            success: false,
            message: $message,
            errors: $errors
        );
    }

    public function toArray(): array
    {
        $response  = [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];

        if($this->errors !== null){
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}
