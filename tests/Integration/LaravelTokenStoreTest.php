<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\LaravelTokenStore;
use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\crm\api\dc\INDataCenter;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\UserSignature;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

class LaravelTokenStoreTest extends IntegrationTestCase
{
    use WithFaker;

    /** @var LaravelTokenStore */
    private $store;
    private $table = 'zoho_oauth_tokens';

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Initializer::getInitializer()->getStore();
    }

    public function test_get_token_by_environment_user_and_client_when_found(): void
    {
        $env = INDataCenter::SANDBOX();
        $data = $this->insertToken(['env' => $env->getName(), 'user_mail' => 'chuck@norris.com', 'client_id' => 'the-client-id']);

        $actual = $this->store->getTokenByEnvironmentUserAndClient($env, new UserSignature('chuck@norris.com'), 'the-client-id');

        self::assertInstanceOf(OAuthToken::class, $actual);
        $this->assertEquals($data['id'], $actual->getId());
        $this->assertEquals($data['user_mail'], $actual->getUserMail());
        $this->assertEquals($data['client_id'], $actual->getClientId());
        $this->assertEquals($data['client_secret'], $actual->getClientSecret());
        $this->assertEquals($data['refresh_token'], $actual->getRefreshToken());
        $this->assertEquals($data['access_token'], $actual->getAccessToken());
        $this->assertEquals($data['grant_token'], $actual->getGrantToken());
        $this->assertEquals(new CarbonImmutable($data['expiry_time']), $actual->getExpiryTime());
        $this->assertEquals($data['redirect_url'], $actual->getRedirectURL());
    }

    public function test_get_token_by_environment_user_and_client_when_not_found(): void
    {
        $this->insertToken(['env' => INDataCenter::DEVELOPER()->getName(), 'user_mail' => 'chuck@norris.com', 'client_id' => 'the-client-id']);

        $actual = $this->store->getTokenByEnvironmentUserAndClient(INDataCenter::PRODUCTION(), new UserSignature('chuck@norris.com'), 'the-client-id');

        self::assertNull($actual);
    }

    public function test_get_tokens(): void
    {
        $data0 = $this->insertToken();
        $data1 = $this->insertToken();

        $actual = $this->store->getTokens();

        $this->assertCount(2, $actual);

        $this->assertInstanceOf(OAuthToken::class, $token0 = $actual[0] ?? null);
        $this->assertEquals($data0['id'], $token0->getId());
        $this->assertEquals($data0['user_mail'], $token0->getUserMail());
        $this->assertEquals($data0['client_id'], $token0->getClientId());
        $this->assertEquals($data0['client_secret'], $token0->getClientSecret());
        $this->assertEquals($data0['refresh_token'], $token0->getRefreshToken());
        $this->assertEquals($data0['access_token'], $token0->getAccessToken());
        $this->assertEquals($data0['grant_token'], $token0->getGrantToken());
        $this->assertEquals(new CarbonImmutable($data0['expiry_time']), $token0->getExpiryTime());
        $this->assertEquals($data0['redirect_url'], $token0->getRedirectURL());

        $this->assertInstanceOf(OAuthToken::class, $token1 = $actual[1] ?? null);
        $this->assertEquals($data1['id'], $token1->getId());
        $this->assertEquals($data1['user_mail'], $token1->getUserMail());
        $this->assertEquals($data1['client_id'], $token1->getClientId());
        $this->assertEquals($data1['client_secret'], $token1->getClientSecret());
        $this->assertEquals($data1['refresh_token'], $token1->getRefreshToken());
        $this->assertEquals($data1['access_token'], $token1->getAccessToken());
        $this->assertEquals($data1['grant_token'], $token1->getGrantToken());
        $this->assertEquals(new CarbonImmutable($data1['expiry_time']), $token1->getExpiryTime());
        $this->assertEquals($data1['redirect_url'], $token1->getRedirectURL());
    }

    public function test_save_token_when_new(): void
    {
        $user = new UserSignature('user@zoho.com');
        $token = $this->makeToken(['user_mail' => 'someone.else@zoho.com']);

        $this->store->saveToken($user, $token);

        $this->assertEquals('user@zoho.com', $token->getUserMail());
        $this->assertDatabaseHasToken($token);
    }

    public function getGrantTokenData(): array
    {
        return [
            'with grant token' => ['a-grant-token'],
            'without grant token' => [null],
        ];
    }

    /**  @dataProvider getGrantTokenData */
    public function test_save_token_when_existing(?string $grant_token): void
    {
        $user = new UserSignature($user_mail = 'user@zoho.com');
        $refresh_token = $this->faker()->md5;
        $deleteData = $this->insertToken(compact('user_mail', 'grant_token', 'refresh_token'));
        $deleteToken = $this->makeToken($deleteData);
        $updatedToken = $this->makeToken($deleteData);
        $updatedToken->setId($this->faker()->md5);
        $updatedToken->setAccessToken($this->faker()->md5);
        $updatedToken->setExpiryTime($this->faker()->dateTime);

        $this->store->saveToken($user, $updatedToken);

        $this->assertDatabaseHasToken($updatedToken);
        $this->assertDatabaseMissing($this->table, [
            'client_id' => $deleteToken->getClientId(),
            'client_secret' => $deleteToken->getClientSecret(),
            'id' => $deleteToken->getId(),
            'grant_token' => $deleteToken->getGrantToken(),
            'refresh_token' => $deleteToken->getRefreshToken(),
            'redirect_url' => $deleteToken->getRedirectUrl(),
            'access_token' => $deleteToken->getAccessToken(),
            'user_mail' => $deleteToken->getUserMail(),
            'expiry_time' => $deleteToken->getExpiryTime()->toDateTimeString(),
        ]);
    }

    public function test_delete_token(): void
    {
        $data = $this->insertToken();
        $token = $this->makeToken($data);

        $this->store->deleteToken($token);

        $this->assertDatabaseMissing($this->table, $data);
    }

    public function test_get_token_by_id(): void
    {
        $data = $this->insertToken(['id' => 'the-id-we-are-looking-for']);
        $token = $this->makeToken(['redirect_url' => 'https://redirect.to/me']);

        $this->store->getTokenById('the-id-we-are-looking-for', $token);

        $this->assertEquals($data['client_id'], $token->getClientId());
        $this->assertEquals($data['client_secret'], $token->getClientSecret());
        $this->assertEquals($data['id'], $token->getId());
        $this->assertEquals($data['grant_token'], $token->getGrantToken());
        $this->assertEquals($data['refresh_token'], $token->getRefreshToken());
        $this->assertEquals('https://redirect.to/me', $token->getRedirectUrl());
        $this->assertEquals($data['access_token'], $token->getAccessToken());
        $this->assertEquals($data['user_mail'], $token->getUserMail());
        $this->assertEquals(new CarbonImmutable($data['expiry_time']), $token->getExpiryTime());
    }

    public function test_delete_tokens(): void
    {
        $this->insertToken();
        $this->insertToken();
        $this->insertToken();

        $this->store->deleteTokens();

        $this->assertDatabaseCount($this->table, 0);
    }

    /**  @dataProvider getGrantTokenData */
    public function test_get_token_with_match(?string $grant_token): void
    {
        $user = new UserSignature('find.me@zoho.com');
        $client_id = $this->faker()->md5;
        $refresh_token = $this->faker()->md5;
        $data = $this->insertToken(compact('client_id', 'grant_token', 'refresh_token') + ['user_mail' => $user->getEmail()]);
        $token = $this->makeToken(compact('client_id', 'grant_token', 'refresh_token') + ['user_mail' => 'forget.me@zoho.com', 'redirect_url' => 'https://redirect.to/me']);

        $actual = $this->store->getToken($user, $token);

        $this->assertSame($token, $actual);
        $this->assertEquals($client_id, $actual->getClientId());
        $this->assertNotEquals($data['client_secret'], $actual->getClientSecret());
        $this->assertEquals($data['id'], $actual->getId());
        $this->assertEquals($grant_token, $actual->getGrantToken());
        $this->assertEquals($refresh_token, $actual->getRefreshToken());
        $this->assertEquals('https://redirect.to/me', $actual->getRedirectUrl());
        $this->assertEquals($data['access_token'], $actual->getAccessToken());
        $this->assertEquals('find.me@zoho.com', $actual->getUserMail());
        $this->assertEquals(new CarbonImmutable($data['expiry_time']), $actual->getExpiryTime());
    }

    private function insertToken(array $data = []): array
    {
        $data = $this->getFakeTokenData($data);
        DB::table('zoho_oauth_tokens')->insert($data);

        return $data;
    }

    private function assertDatabaseHasToken(OAuthToken $token): void
    {
        $this->assertDatabaseHas($this->table, [
            'id' => $token->getId(),
            'env' => Initializer::getInitializer()->getEnvironment()->getName(),
            'user_mail' => $token->getUserMail(),
            'client_id' => $token->getClientId(),
            'client_secret' => $token->getClientSecret(),
            'refresh_token' => $token->getRefreshToken(),
            'access_token' => $token->getAccessToken(),
            'grant_token' => $token->getGrantToken(),
            'expiry_time' => $token->getExpiryTime()->toDateTimeString(),
            'redirect_url' => $token->getRedirectUrl(),
        ]);
    }

    private function getFakeTokenData(array $data = [])
    {
        return array_merge([
            'id' => $this->faker()->md5,
            'env' => Initializer::getInitializer()->getEnvironment()->getName(),
            'user_mail' => $this->faker()->email,
            'client_id' => $this->faker()->md5,
            'client_secret' => $this->faker()->md5,
            'refresh_token' => $this->faker()->md5,
            'access_token' => $this->faker()->md5,
            'grant_token' => $this->faker()->md5,
            'expiry_time' => $this->faker()->dateTime->format('Y-m-d H:i:s'),
            'redirect_url' => $this->faker()->url,
        ], $data);
    }

    private function makeToken(array $data = []): OAuthToken
    {
        $data = $this->getFakeTokenData($data);
        return new OAuthToken(
            $data['client_id'],
            $data['client_secret'],
            $data['id'],
            $data['grant_token'],
            $data['refresh_token'],
            $data['redirect_url'],
            $data['access_token'],
            $data['user_mail'],
            new CarbonImmutable($data['expiry_time']),
        );
    }
}
