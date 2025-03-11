<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AccessDeniedException extends HttpException
{
    public function __construct(string $message = 'Access denied.', int $statusCode = 404)
    {
        parent::__construct($statusCode, $message);
    }
}