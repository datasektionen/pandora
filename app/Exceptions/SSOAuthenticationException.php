<?php

namespace App\Exceptions;

/**
 * Exception thrown when SSO authentication fails.
 */
class SSOAuthenticationException extends SSOException
{
    /**
     * Create a new SSO authentication exception.
     *
     * @param string $message
     * @param array $context
     * @param \Exception|null $previous
     */
    public function __construct($message = "SSO authentication failed", array $context = [], $previous = null)
    {
        parent::__construct($message, 401, $previous, array_merge($context, [
            'type' => 'authentication_error'
        ]));
    }
}