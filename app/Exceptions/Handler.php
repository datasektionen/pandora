<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Enhanced logging for SSO exceptions
            if ($e instanceof SSOException) {
                Log::error('SSO Exception occurred', [
                    'exception_type' => get_class($e),
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        });

        $this->renderable(function (SSOException $e, Request $request) {
            return $this->handleSSOException($e, $request);
        });
    }

    /**
     * Handle SSO-specific exceptions with appropriate responses.
     *
     * @param SSOException $e
     * @param Request $request
     * @return Response|null
     */
    protected function handleSSOException(SSOException $e, Request $request)
    {
        // Log the exception with context
        Log::error('Handling SSO exception', [
            'exception_type' => get_class($e),
            'message' => $e->getMessage(),
            'context' => $e->getContext(),
            'url' => $request->fullUrl(),
            'user_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Handle different types of SSO exceptions
        if ($e instanceof SSOConfigurationException) {
            return $this->handleConfigurationException($e, $request);
        }

        if ($e instanceof SSOServiceUnavailableException) {
            return $this->handleServiceUnavailableException($e, $request);
        }

        if ($e instanceof SSOAuthenticationException) {
            return $this->handleAuthenticationException($e, $request);
        }

        if ($e instanceof SSOPermissionException) {
            return $this->handlePermissionException($e, $request);
        }

        // Default SSO exception handling
        return redirect('/login')
            ->with('error', 'Ett fel uppstod med inloggningssystemet. Försök igen senare.');
    }

    /**
     * Handle SSO configuration exceptions.
     *
     * @param SSOConfigurationException $e
     * @param Request $request
     * @return Response
     */
    protected function handleConfigurationException(SSOConfigurationException $e, Request $request)
    {
        // In production, show generic error to users
        if (app()->environment('production')) {
            return redirect('/')
                ->with('error', 'Inloggningssystemet är för närvarande otillgängligt. Kontakta systemadministratören.');
        }

        // In development, show detailed error
        return redirect('/')
            ->with('error', 'SSO Configuration Error: ' . $e->getMessage());
    }

    /**
     * Handle SSO service unavailable exceptions.
     *
     * @param SSOServiceUnavailableException $e
     * @param Request $request
     * @return Response
     */
    protected function handleServiceUnavailableException(SSOServiceUnavailableException $e, Request $request)
    {
        return redirect('/login')
            ->with('error', 'Inloggningssystemet är för närvarande otillgängligt. Försök igen om några minuter.');
    }

    /**
     * Handle SSO authentication exceptions.
     *
     * @param SSOAuthenticationException $e
     * @param Request $request
     * @return Response
     */
    protected function handleAuthenticationException(SSOAuthenticationException $e, Request $request)
    {
        return redirect('/login')
            ->with('error', 'Inloggning misslyckades. Kontrollera dina inloggningsuppgifter och försök igen.');
    }

    /**
     * Handle SSO permission exceptions.
     *
     * @param SSOPermissionException $e
     * @param Request $request
     * @return Response
     */
    protected function handlePermissionException(SSOPermissionException $e, Request $request)
    {
        return redirect('/')
            ->with('error', 'Du har inte behörighet att komma åt denna resurs.');
    }
}
