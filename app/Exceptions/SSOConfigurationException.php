<?php

namespace App\Exceptions;

/**
 * Exception thrown when SSO configuration is invalid or missing.
 */
class SSOConfigurationException extends SSOException
{
    /**
     * Create a new SSO configuration exception.
     *
     * @param string $configKey The missing or invalid configuration key
     * @param string $message Custom error message
     */
    public function __construct($configKey, $message = null)
    {
        $message = $message ?: "Missing or invalid SSO configuration: {$configKey}";
        
        parent::__construct($message, 500, null, [
            'config_key' => $configKey,
            'type' => 'configuration_error'
        ]);
    }
}