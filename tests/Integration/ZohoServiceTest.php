<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\ZohoModule;
use Asciisd\Zoho\CriteriaBuilder;
use Asciisd\Zoho\Facades\ZohoManager;
use Asciisd\Zoho\ZohoOrganization;
use Asciisd\Zoho\ZohoService;
use Carbon\Carbon;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\modules\Module;
use com\zoho\crm\api\modules\ResponseWrapper;
use com\zoho\crm\api\profiles\Profile;
use com\zoho\crm\api\UserSignature;
use DateTime;
use Mockery;

class ZohoServiceTest extends IntegrationTestCase
{
    /** @var ZohoService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ZohoService;
    }

    public function test_it_can_generate_an_access_token(): void
    {
        $token = Mockery::mock(OAuthToken::class);
        $user = Mockery::mock(UserSignature::class);
        $store = Mockery::mock(TokenStore::class);
        $initializer = Mockery::mock(Initializer::class);
        $initializer->shouldReceive(['getToken' => $token, 'getUser' => $user, 'getStore' => $store]);
        ZohoService::setInitializer($initializer);
        $token->shouldReceive('setGrantToken')->with('new-grant-token')->once();
        $token->shouldReceive('generateAccessToken')->with($user, $store)->once();

        ZohoService::generateAccessToken('new-grant-token');
    }

    public function test_it_rethrows_exception_when_generate_access_token_throws_exception(): void
    {
        $token = Mockery::mock(OAuthToken::class);
        $user = Mockery::mock(UserSignature::class);
        $store = Mockery::mock(TokenStore::class);
        $initializer = Mockery::mock(Initializer::class);
        $initializer->shouldReceive(['getToken' => $token, 'getUser' => $user, 'getStore' => $store]);
        ZohoService::setInitializer($initializer);
        $token->shouldReceive('setGrantToken');
        $exception = Mockery::mock(SDKException::class);
        $token->shouldReceive('generateAccessToken')->andThrow($exception);

        self::expectExceptionObject($exception);
        ZohoService::generateAccessToken('new-grant-token');
    }

    public function getModuleNameData(): array
    {
        return [
            'contacts' => ['Contacts'],
            'leads' => ['Leads'],
            'tags' => ['Tags'],
        ];
    }

    /** @dataProvider getModuleNameData */
    public function test_it_returns_a_module_from_use_module(string $name): void
    {
        $actual = $this->service->useModule($name);

        self::assertEquals(new ZohoModule($name), $actual);
    }

    public function test_it_returns_a_zoho_org_from_current_org(): void
    {
        $actual = $this->service->currentOrg();

        self::assertEquals(new ZohoOrganization, $actual);
    }
}
