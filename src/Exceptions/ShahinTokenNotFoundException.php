<?php

namespace Mrmr7\LaravelShahin\Exceptions;

use Exception;
use Mrmr7\LaravelShahin\Contracts\ShahinExceptionInterface;

class ShahinTokenNotFoundException extends Exception implements ShahinExceptionInterface
{
    public function __construct($message, $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
