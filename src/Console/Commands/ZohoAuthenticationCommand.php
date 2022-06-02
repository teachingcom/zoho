<?php

namespace Asciisd\Zoho\Console\Commands;

use Asciisd\Zoho\Facades\ZohoManager;
use com\zoho\crm\api\Initializer;
use Illuminate\Console\Command;

class ZohoAuthenticationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:authentication';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OAuth url to complete the Authentication process.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Copy the following url, paste in browser, and hit return.');
        $this->line(
            ZohoManager::getEnvironment()->getAccountsAuthUrl()
            . '?' . http_build_query([
                'scope' => config('zoho.oauth_scope'),
                'client_id' => config('zoho.client_id'),
                'response_type' => 'code',
                'access_type' => 'offline',
                'redirect_uri' => route('zoho.oauth2callback'),
            ])
        );
    }
}
