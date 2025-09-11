<?php

namespace App\Services;

use App\Exceptions\SSOConfigurationException;
use Illuminate\Support\Facades\Log;

/**
 * Service for validating SSO configuration on application startup.
 */
class SSOConfigValidator
{
    /**
     * Required configuration keys for SSO to function.
     */
    const REQUIRED_CONFIG = [
        'sso.client_id' => 'SSO_CLIENT_ID',
        'sso.client_secret' => 'SSO_CLIENT_SECRET',
        'sso.issuer_url' => 'SSO_ISSUER_URL',
        'sso.redirect_uri' => 'SSO_REDIRECT_URI',
    ];

    /**
     * Optional configuration keys with defaults.
     */
    const OPTIONAL_CONFIG = [
        'sso.logout_redirect_uri' => 'SSO_LOGOUT_REDIRECT_URI',
        'sso.verify_ssl' => 'SSO_VERIFY_SSL',
        'sso.timeout' => 'SSO_TIMEOUT',
        'sso.scopes' => null,
    ];

    /**
     * Validate all SSO configuration.
     *
     * @throws SSOConfigurationException
     * @return bool
     */
    public function validate()
    {
        $errors = [];

        // Check required configuration
        foreach (self::REQUIRED_CONFIG as $configKey => $envKey) {
            $value = config($configKey);
            
            if (empty($value)) {
                $errors[] = "Missing required configuration: {$configKey} (environment variable: {$envKey})";
            }
        }

        // Validate URL formats
        $this->validateUrls($errors);

        // Validate scopes
        $this->validateScopes($errors);

        // Validate timeout
        $this->validateTimeout($errors);

        if (!empty($errors)) {
            $errorMessage = "SSO configuration validation failed:\n" . implode("\n", $errors);
            
            Log::critical('SSO configuration validation failed', [
                'errors' => $errors,
                'config_check' => 'startup'
            ]);

            throw new SSOConfigurationException('multiple', $errorMessage);
        }

        Log::info('SSO configuration validation passed', [
            'issuer_url' => config('sso.issuer_url'),
            'client_id' => substr(config('sso.client_id'), 0, 8) . '...',
            'scopes' => config('sso.scopes'),
            'verify_ssl' => config('sso.verify_ssl'),
            'timeout' => config('sso.timeout')
        ]);

        return true;
    }

    /**
     * Validate URL configurations.
     *
     * @param array &$errors
     */
    protected function validateUrls(array &$errors)
    {
        $urlConfigs = [
            'sso.issuer_url' => 'Issuer URL',
            'sso.redirect_uri' => 'Redirect URI',
            'sso.logout_redirect_uri' => 'Logout Redirect URI'
        ];

        foreach ($urlConfigs as $configKey => $description) {
            $url = config($configKey);
            
            if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "{$description} is not a valid URL: {$url}";
            }
        }

        // Validate that redirect URI matches application URL
        $redirectUri = config('sso.redirect_uri');
        $appUrl = config('app.url');
        
        if ($redirectUri && $appUrl && !str_starts_with($redirectUri, $appUrl)) {
            Log::warning('SSO redirect URI does not match application URL', [
                'redirect_uri' => $redirectUri,
                'app_url' => $appUrl
            ]);
        }
    }

    /**
     * Validate scopes configuration.
     *
     * @param array &$errors
     */
    protected function validateScopes(array &$errors)
    {
        $scopes = config('sso.scopes');
        
        if (!is_array($scopes)) {
            $errors[] = "SSO scopes must be an array";
            return;
        }

        $requiredScopes = ['openid'];
        $recommendedScopes = ['profile', 'email', 'permissions'];

        foreach ($requiredScopes as $requiredScope) {
            if (!in_array($requiredScope, $scopes)) {
                $errors[] = "Missing required scope: {$requiredScope}";
            }
        }

        foreach ($recommendedScopes as $recommendedScope) {
            if (!in_array($recommendedScope, $scopes)) {
                Log::warning("Missing recommended scope: {$recommendedScope}", [
                    'current_scopes' => $scopes
                ]);
            }
        }
    }

    /**
     * Validate timeout configuration.
     *
     * @param array &$errors
     */
    protected function validateTimeout(array &$errors)
    {
        $timeout = config('sso.timeout');
        
        if ($timeout !== null) {
            if (!is_numeric($timeout) || $timeout <= 0) {
                $errors[] = "SSO timeout must be a positive number (seconds)";
            } elseif ($timeout < 5) {
                Log::warning('SSO timeout is very low, may cause connection issues', [
                    'timeout' => $timeout
                ]);
            } elseif ($timeout > 120) {
                Log::warning('SSO timeout is very high, may cause poor user experience', [
                    'timeout' => $timeout
                ]);
            }
        }
    }

    /**
     * Get a summary of current SSO configuration (safe for logging).
     *
     * @return array
     */
    public function getConfigSummary()
    {
        return [
            'issuer_url' => config('sso.issuer_url'),
            'client_id_prefix' => substr(config('sso.client_id'), 0, 8) . '...',
            'has_client_secret' => !empty(config('sso.client_secret')),
            'redirect_uri' => config('sso.redirect_uri'),
            'logout_redirect_uri' => config('sso.logout_redirect_uri'),
            'scopes' => config('sso.scopes'),
            'verify_ssl' => config('sso.verify_ssl'),
            'timeout' => config('sso.timeout')
        ];
    }
}