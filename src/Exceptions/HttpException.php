<?php

namespace Ounzy\FrogFramework\Exceptions;

use Exception;

class HttpException extends Exception
{
    public function __construct(public int $statusCode, string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
