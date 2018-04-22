<?php

namespace src\exceptions;

class HttpException extends \Exception
{
    public function __construct($message = null, $code = 0)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        die(json_encode([
            'code' => $code,
            'message' => $message
        ]));
    }
}