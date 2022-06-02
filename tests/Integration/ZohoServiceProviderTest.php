<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\LaravelTokenStore;
use Asciisd\Zoho\ZohoService;
use com\zoho\api\logger\SDKLogger;
use com\zoho\crm\api\dc\JPDataCenter;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\Initializer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZohoServiceProviderTest extends IntegrationTestCase
{
    public function test_it_resolves_to_zoho_service_without_existing_token(): void
    {
        Config::set('zoho.client_id', 'the-client-id');
        Config::set('zoho.client_secret', 'the-client-secret');
        Config::set('zoho.datacenter_name', 'us_dev');
        Config::set('zoho.db_connection_name', 'testbench');
        Config::set('zoho.current_user_email', 'user@zoho.com');
        Config::set('zoho.redirect_uri', 'https://localhost');
        Config::set('zoho.grant_token', 'the-grant-token');
        Config::set('zoho.logger_channel', 'default');
        Config::set('zoho.auto_refresh_fields', false);
        Config::set('zoho.pick_list_validation', true);

        $actual = $this->app->get('zoho_manager');

        self::assertInstanceOf(ZohoService::class, $actual);
        self::assertNotNull($initializer = Initializer::getInitializer());
        self::assertSame($initializer, ZohoService::getInitializer());
        self::assertEquals(USDataCenter::DEVELOPER(), $initializer->getEnvironment());
        self::assertEquals(Log::channel('default'), SDKLogger::getLogger());
        self::assertFalse($initializer->getSDKConfig()->getAutoRefreshFields());
        self::assertTrue($initializer->getSDKConfig()->getPickListValidation());
        self::assertInstanceOf(LaravelTokenStore::class, $initializer->getStore());
        $token = $initializer->getToken();
        self::assertNull($token->getId());
        self::assertEquals('user@zoho.com', $token->getUserMail());
        self::assertEquals('the-client-id', $token->getClientId());
        self::assertEquals('the-client-secret', $token->getClientSecret());
        self::assertNull($token->getRefreshToken());
        self::assertNull($token->getAccessToken());
        self::assertEquals('the-grant-token', $token->getGrantToken());
        self::assertEquals($this->now(), $token->getExpiryTime());
        self::assertEquals('https://localhost', $token->getRedirectURL());
        self::assertEquals('user@zoho.com', $initializer->getUser()->getEmail());
    }

    public function test_it_resolves_to_zoho_service_with_existing_token(): void
    {
        Config::set('zoho.client_id', 'the-client-id');
        Config::set('zoho.client_secret', 'the-client-secret');
        Config::set('zoho.datacenter_name', 'jp_sdb');
        Config::set('zoho.db_connection_name', 'testbench');
        Config::set('zoho.current_user_email', 'chuck@norris.com');
        Config::set('zoho.redirect_uri', 'https://localhost');
        Config::set('zoho.grant_token', 'config-grant-token');
        Config::set('zoho.logger_channel', 'default');
        Config::set('zoho.auto_refresh_fields', true);
        Config::set('zoho.pick_list_validation', false);
        DB::table('zoho_oauth_tokens')->insert([
            'id' => 'record-id',
            'env' => 'jp_sdb',
            'user_mail' => 'chuck@norris.com',
            'client_id' => 'the-client-id',
            'client_secret' => 'the-client-secret',
            'refresh_token' => 'the-refresh-token',
            'access_token' => 'the-access-token',
            'grant_token' => 'record-grant-token',
            'expiry_time' => $expiry = $this->now()->addMinutes(14),
            'redirect_url' => 'https://another.host',
        ]);

        $actual = $this->app->get('zoho_manager');

        self::assertInstanceOf(ZohoService::class, $actual);
        self::assertNotNull($initializer = Initializer::getInitializer());
        self::assertSame($initializer, ZohoService::getInitializer());
        self::assertEquals(JPDataCenter::SANDBOX(), $initializer->getEnvironment());
        self::assertEquals(Log::channel('default'), SDKLogger::getLogger());
        self::assertTrue($initializer->getSDKConfig()->getAutoRefreshFields());
        self::assertFalse($initializer->getSDKConfig()->getPickListValidation());
        self::assertInstanceOf(LaravelTokenStore::class, $initializer->getStore());
        $token = $initializer->getToken();
        self::assertEquals('record-id', $token->getId());
        self::assertEquals('chuck@norris.com', $token->getUserMail());
        self::assertEquals('the-client-id', $token->getClientId());
        self::assertEquals('the-client-secret', $token->getClientSecret());
        self::assertEquals('the-refresh-token', $token->getRefreshToken());
        self::assertEquals('the-access-token', $token->getAccessToken());
        self::assertEquals('record-grant-token', $token->getGrantToken());
        self::assertEquals($expiry, $token->getExpiryTime());
        self::assertEquals('https://another.host', $token->getRedirectURL());
        self::assertEquals('chuck@norris.com', $initializer->getUser()->getEmail());
    }
}
