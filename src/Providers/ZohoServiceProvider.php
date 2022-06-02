<?php

namespace Asciisd\Zoho\Providers;

use Asciisd\Zoho\Console\Commands\ZohoAuthenticationCommand;
use Asciisd\Zoho\Console\Commands\ZohoSetupCommand;
use Asciisd\Zoho\LaravelTokenStore;
use Asciisd\Zoho\ZohoService;
use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\UserSignature;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ZohoServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->registerCommands();
        $this->registerSingleton();
    }

    public function provides(): array
    {
        return ['zoho_manager'];
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/../../config/zoho.php' => config_path('zoho.php'),
        ], 'zoho-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'zoho-migrations');
    }

    /**
     * Setup the configuration for Zoho.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/zoho.php', 'zoho'
        );
    }

    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ZohoSetupCommand::class,
                ZohoAuthenticationCommand::class
            ]);
        }
    }

    private function registerSingleton()
    {
        $this->app->bind('zoho_manager', function () {
            $clientId = config('zoho.client_id');
            $env = Environment::getByName(config('zoho.datacenter_name'));
            $store = new LaravelTokenStore(DB::connection(config('zoho.db_connection_name')));
            $user = new UserSignature(config('zoho.current_user_email'));
            if (!($token = $store->getTokenByEnvironmentUserAndClient($env, $user, $clientId))) {
                $token = (new OAuthBuilder)
                    ->userMail($user->getEmail())
                    ->clientId($clientId)
                    ->clientSecret(config('zoho.client_secret'))
                    ->redirectURL(config('zoho.redirect_uri'))
                    ->grantToken(config('zoho.grant_token'))
                    ->build();
            }

            (new InitializeBuilder)
                ->environment($env)
                ->logger(Log::channel(config('zoho.logger_channel')))
                ->SDKConfig(new SDKConfig(config('zoho.auto_refresh_fields'), config('zoho.pick_list_validation')))
                ->store($store)
                ->token($token)
                ->user($user)
                ->initialize();
            ZohoService::setInitializer(Initializer::getInitializer());

            return new ZohoService();
        });
    }
}
