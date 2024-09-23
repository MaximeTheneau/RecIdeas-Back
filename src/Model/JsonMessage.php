<?php

namespace App\Model;


class JsonMessage{

    public $message;
    public $code;

    public function __construct(string $message, int $code)
    {
        $this->message = $message;
        $this->code = $code;
    }
}