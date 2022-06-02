<?php

namespace Asciisd\Zoho\Exceptions;

use com\zoho\crm\api\util\APIException as SDKAPIException;
use Exception;

class APIException extends Exception
{
    private $sdkException;

    public function __construct(SDKAPIException $sdkException)
    {
        $this->sdkException = $sdkException;
        parent::__construct($sdkException->getMessage()->getValue());
    }

    public function getStatus(): string
    {
        return $this->sdkException->getStatus()->getValue();
    }

    public function getErrorCode(): string
    {
        return $this->sdkException->getCode()->getValue();
    }

    public function getDetails(): array
    {
        return $this->sdkException->getDetails();
    }

    public function getSdkException(): SDKAPIException
    {
        return $this->sdkException;
    }
}
