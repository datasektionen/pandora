<?php

namespace App\Exceptions;

/**
 * Exception thrown when there are permission-related errors.
 */
class SSOPermissionException extends SSOException
{
    /**
     * Create a new SSO permission exception.
     *
     * @param string $message
     * @param array $context
     * @param \Exception|null $previous
     */
    public function __construct($message = "Permission validation failed", array $context = [], $previous = null)
    {
        parent::__construct($message, 403, $previous, array_merge($context, [
            'type' => 'permission_error'
        ]));
    }
}