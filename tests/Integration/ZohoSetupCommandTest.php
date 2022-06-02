<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\Facades\ZohoManager;
use Illuminate\Support\Facades\Config;

class ZohoSetupCommandTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('zoho.client_id', 'the-client-id');
        Config::set('zoho.client_secret', 'the-client-secret');
        Config::set('zoho.datacenter_name', 'us_dev');
        Config::set('zoho.db_connection_name', 'testbench');
        Config::set('zoho.current_user_email', 'user@zoho.com');
        Config::set('zoho.redirect_uri', 'https://localhost');
    }

    public function test_it_generates_access_token(): void
    {
        ZohoManager::shouldReceive('generateAccessToken')->with('the-grant-token')->once();

        $this->artisan('zoho:grant', ['token' => 'the-grant-token'])
            ->expectsOutput('Zoho CRM has been set up successfully.')
            ->assertExitCode(0);
    }

    public function test_it_displays_error_when_token_arg_empty(): void
    {
        ZohoManager::shouldReceive('generateAccessToken')->never();

        $this->artisan('zoho:grant', ['token' => ''])
            ->expectsOutput('The Grant Token is required.')
            ->assertExitCode(0);
    }
}
