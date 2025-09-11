<?php

namespace App\Exceptions;

/**
 * Exception thrown when the SSO service is unavailable.
 */
class SSOServiceUnavailableException extends SSOException
{
    /**
     * Create a new SSO service unavailable exception.
     *
     * @param string $message
     * @param array $context
     * @param \Exception|null $previous
     */
    public function __construct($message = "SSO service is currently unavailable", array $context = [], $previous = null)
    {
        parent::__construct($message, 503, $previous, array_merge($context, [
            'type' => 'service_unavailable'
        ]));
    }
}