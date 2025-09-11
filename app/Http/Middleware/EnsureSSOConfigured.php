<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SSOConfigValidator;
use App\Exceptions\SSOConfigurationException;

/**
 * Middleware to ensure SSO is properly configured before allowing access.
 */
class EnsureSSOConfigured
{
    /**
     * The SSO configuration validator.
     *
     * @var SSOConfigValidator
     */
    protected $configValidator;

    /**
     * Create a new middleware instance.
     *
     * @param SSOConfigValidator $configValidator
     */
    public function __construct(SSOConfigValidator $configValidator)
    {
        $this->configValidator = $configValidator;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if there's a stored configuration error
            if (config('sso.configuration_error')) {
                throw new SSOConfigurationException('stored', config('sso.configuration_error'));
            }

            // Validate SSO configuration
            $this->configValidator->validate();

            return $next($request);

        } catch (SSOConfigurationException $e) {
            Log::warning('SSO access blocked due to configuration error', [
                'url' => $request->fullUrl(),
                'error' => $e->getMessage(),
                'user_ip' => $request->ip()
            ]);

            return redirect('/')
                ->with('error', 'Inloggningssystemet är för närvarande otillgängligt på grund av konfigurationsproblem. Kontakta systemadministratören.');
        }
    }
}