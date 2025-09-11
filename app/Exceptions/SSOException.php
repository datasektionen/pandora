<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception class for SSO-related errors.
 */
class SSOException extends Exception
{
    /**
     * The error context for logging.
     *
     * @var array
     */
    protected $context = [];

    /**
     * Create a new SSO exception instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param array $context
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the error context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the error context.
     *
     * @param array $context
     * @return $this
     */
    public function setContext(array $context)
    {
        $this->context = $context;
        return $this;
    }
}