<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Services\SSOConfigValidator;
use App\Exceptions\SSOConfigurationException;

/**
 * Service provider for SSO configuration and validation.
 */
class SSOServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SSOConfigValidator::class, function ($app) {
            return new SSOConfigValidator();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Only validate configuration when SSO is likely to be used
        // Skip validation during basic application bootstrap
        if ($this->shouldValidateConfiguration()) {
            $this->validateSSOConfiguration();
        }
    }

    /**
     * Determine if SSO configuration should be validated now.
     *
     * @return bool
     */
    protected function shouldValidateConfiguration()
    {
        // Skip validation during artisan commands that don't need SSO
        if ($this->app->runningInConsole()) {
            $command = $_SERVER['argv'][1] ?? '';
            
            // Only validate for SSO-related commands
            $ssoCommands = ['sso:health-check', 'serve', 'queue:work'];
            
            foreach ($ssoCommands as $ssoCommand) {
                if (str_contains($command, $ssoCommand)) {
                    return true;
                }
            }
            
            return false;
        }

        // For web requests, always validate SSO configuration
        // This ensures configuration errors are caught early and stored
        return true;
    }

    /**
     * Validate SSO configuration on application startup.
     *
     * @return void
     */
    protected function validateSSOConfiguration()
    {
        try {
            $validator = $this->app->make(SSOConfigValidator::class);
            $validator->validate();
            
        } catch (SSOConfigurationException $e) {
            // Log the configuration error
            Log::warning('SSO configuration validation failed during application startup', [
                'error' => $e->getMessage(),
                'context' => $e->getContext()
            ]);

            // Store the configuration error for later display
            config(['sso.configuration_error' => $e->getMessage()]);
            
            // Don't throw exceptions during startup - handle gracefully
            Log::info('SSO features will be disabled due to configuration issues');
        }
    }
}