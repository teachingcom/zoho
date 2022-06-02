<?php

namespace Asciisd\Zoho;

use Asciisd\Zoho\Exceptions\APIException;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\modules\Module;

/**
 * Class ZohoService
 *
 * @package Asciisd\Zoho
 */
class ZohoService
{
    /** @var Initializer|null */
    private static $initializer = null;

    public static function getInitializer(): ?Initializer
    {
        return self::$initializer;
    }

    public static function setInitializer(Initializer $initializer): void
    {
        self::$initializer = $initializer;
    }

    /**
     * Generates the access token from the given grant token.
     * @throws SDKException
     */
    public static function generateAccessToken(string $grantToken): void
    {
        $token = self::$initializer->getToken();
        $token->setGrantToken($grantToken);
        $token->generateAccessToken(self::$initializer->getUser(), self::$initializer->getStore());
    }

    /**
     * Fetches a list of all modules.
     *
     * @return Module[]
     * @throws APIException
     */
    public function getAllModules(): array
    {
        return (new ZohoModule)->getAllModules();
    }

    /**
     * Provides a `ZohoModule` instance configured with the given name.
     */
    public function useModule(string $module_api_name = 'Leads'): ZohoModule
    {
        return new ZohoModule($module_api_name);
    }

    public function currentOrg(): ZohoOrganization
    {
        return new ZohoOrganization();
    }

    public function getEnvironment(): Environment
    {
        return self::$initializer->getEnvironment();
    }
}
