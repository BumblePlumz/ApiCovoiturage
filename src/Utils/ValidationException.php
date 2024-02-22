<?php

namespace App\Utils;

class ValidationException extends \Exception
{
    private $httpStatusCode;

    public function __construct($message, $httpStatusCode = 400)
    {
        parent::__construct($message);
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}
