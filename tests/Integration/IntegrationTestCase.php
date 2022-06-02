<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\LaravelTokenStore;
use Asciisd\Zoho\Tests\TestCase;
use Asciisd\Zoho\ZohoService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\UserSignature;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use Monolog\Logger;

abstract class IntegrationTestCase extends TestCase
{
    use RefreshDatabase;

    /** @var Logger|MockInterface */
    protected $logger;
    /** @var MockHandler */
    protected $sdkClientMock;
    /** @var Client */
    protected $sdkClient;

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow($this->now());
        CarbonImmutable::setTestNow($this->now());

        $this->logger = Mockery::mock(Logger::class);
        $this->logger->shouldReceive(['info' => null, 'emergency' => null])->byDefault();
        Log::shouldReceive('channel')->andReturn($this->logger);

        $this->sdkClientMock = new MockHandler;
        $this->sdkClient = new Client(['handler' => HandlerStack::create($this->sdkClientMock)]);

        (new InitializeBuilder)
            ->client($this->sdkClient)
            ->environment(USDataCenter::DEVELOPER())
            ->logger(Log::channel('zoho'))
            ->SDKConfig(new SDKConfig(false, true))
            ->store(new LaravelTokenStore($this->getConnection()))
            ->token(new OAuthToken(
                'the-client-id',
                'the-client-secret',
                'the-token-id',
                'the-grant-token',
                'the-refresh-token',
                'https://redirect.to',
                'the-access-token',
                'abc@zoho.com',
                $this->now()->addHour(),
            ))
            ->user(new UserSignature('abc@zoho.com'))
            ->initialize();

        ZohoService::setInitializer(Initializer::getInitializer()->setClient($this->sdkClient));
    }

    protected function now(): CarbonImmutable
    {
        return new CarbonImmutable('2022-06-03 01:23:45');
    }

    protected function makeJsonResponse(int $status = null, string $body = null): Response
    {
        return new Response($status, ['content-type' => 'application/json'], $body);
    }
}
