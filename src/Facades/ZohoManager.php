<?php

namespace Asciisd\Zoho\Facades;

use Asciisd\Zoho\ZohoModule;
use Asciisd\Zoho\ZohoOrganization;
use com\zoho\crm\api\dc\Environment;
use Illuminate\Support\Facades\Facade;

/**
 * Class Zoho
 *
 * @method static ZohoModule useModule(string $module_api_name = 'Leads')
 * @method static ZohoOrganization currentOrg()
 * @method static generateAccessToken(string $grantToken)
 * @method static Environment getEnvironment()
 *
 * @package App\Facades
 */
class ZohoManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zoho_manager';
    }
}
