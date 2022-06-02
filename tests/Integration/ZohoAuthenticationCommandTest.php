<?php

namespace Asciisd\Zoho\Tests\Integration;

use Illuminate\Support\Facades\Config;

class ZohoAuthenticationCommandTest extends IntegrationTestCase
{
    public function test_handle(): void
    {
        Config::set('zoho.client_id', 'the-client-id');
        Config::set('zoho.client_secret', 'the-client-secret');
        Config::set('zoho.datacenter_name', 'us_sdb');
        Config::set('zoho.db_connection_name', 'testbench');
        Config::set('zoho.current_user_email', 'user@zoho.com');
        Config::set('zoho.oauth_scope', 'ZohoCRM.modules.ALL');

        $this->artisan('zoho:authentication')
            ->expectsOutput('Copy the following url, paste in browser, and hit return.')
            ->expectsOutput(
                'https://accounts.zoho.com/oauth/v2/auth'
                . '?scope=ZohoCRM.modules.ALL'
                . '&client_id=the-client-id'
                . '&response_type=code'
                . '&access_type=offline'
                . '&redirect_uri=' . urlencode('http://localhost/zoho/oauth2callback')
            )
            ->assertExitCode(0);
    }
}
