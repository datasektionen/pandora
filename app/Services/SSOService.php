<?php

namespace App\Services;

use Jumbojett\OpenIDConnectClient;
use Illuminate\Support\Facades\Log;
use App\Exceptions\SSOException;
use App\Exceptions\SSOConfigurationException;
use App\Exceptions\SSOAuthenticationException;
use App\Exceptions\SSOServiceUnavailableException;
use App\Services\SSOConfigValidator;
use Exception;

class SSOService
{
    /**
     * The OpenID Connect client instance.
     *
     * @var OpenIDConnectClient
     */
    protected $client;

    /**
     * The configuration validator instance.
     *
     * @var SSOConfigValidator
     */
    protected $configValidator;

    /**
     * Create a new SSO service instance.
     *
     * @param SSOConfigValidator $configValidator
     * @throws SSOException
     */
    public function __construct(SSOConfigValidator $configValidator)
    {
        $this->configValidator = $configValidator;
        $this->initializeClient();
    }

    /**
     * Initialize the OpenID Connect client with configuration.
     *
     * @throws SSOException
     */
    protected function initializeClient()
    {
        try {
            // Check for configuration errors from startup
            if (config('sso.configuration_error')) {
                throw new SSOConfigurationException('startup', config('sso.configuration_error'));
            }

            // Validate configuration - this will throw if config is missing
            $this->configValidator->validate();

            // Initialize the OpenID Connect client following the library documentation
            $this->client = new OpenIDConnectClient(
                config('sso.issuer_url'),
                config('sso.client_id'),
                config('sso.client_secret')
            );

            // Configure the client according to the OIDC library documentation
            $this->client->setRedirectURL(config('sso.redirect_uri'));
            
            // Add scopes using addScope method - pass all scopes as array
            $this->client->addScope(config('sso.scopes'));

            // Configure SSL verification settings
            $this->client->setVerifyHost(config('sso.verify_ssl'));
            $this->client->setVerifyPeer(config('sso.verify_ssl'));
            
            // Set timeout for HTTP requests
            $this->client->setTimeout(config('sso.timeout'));

            Log::info('SSO client initialized successfully', [
                'issuer_url' => config('sso.issuer_url'),
                'scopes' => config('sso.scopes'),
                'redirect_uri' => config('sso.redirect_uri')
            ]);

        } catch (SSOConfigurationException $e) {
            Log::error('SSO client initialization failed due to configuration error', [
                'error' => $e->getMessage(),
                'context' => $e->getContext()
            ]);
            throw $e;

        } catch (Exception $e) {
            Log::error('SSO client initialization failed', [
                'error' => $e->getMessage()
            ]);
            
            throw new SSOServiceUnavailableException(
                'Failed to initialize SSO client: ' . $e->getMessage(),
                ['original_error' => $e->getMessage()],
                $e
            );
        }
    }

    /**
     * Authenticate the user using the simplified OIDC flow.
     * This method handles both the initial redirect and the callback processing.
     *
     * @return array User data and permissions
     * @throws SSOAuthenticationException|SSOServiceUnavailableException
     */
    public function authenticate()
    {
        $startTime = microtime(true);

        try {
            // Check if this is a callback with an error parameter
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                $errorDescription = $_GET['error_description'] ?? '';
                $errorUri = $_GET['error_uri'] ?? '';

                Log::error('SSO provider returned error in callback', [
                    'error' => $error,
                    'error_description' => $errorDescription,
                    'error_uri' => $errorUri,
                    'query_params' => $_GET
                ]);

                // Map common OAuth2/OIDC errors to user-friendly messages
                $userMessage = $this->mapErrorToUserMessage($error, $errorDescription);
                
                throw new SSOAuthenticationException($userMessage, [
                    'oauth_error' => $error,
                    'oauth_error_description' => $errorDescription,
                    'oauth_error_uri' => $errorUri
                ]);
            }

            // Check if this is a callback but missing the authorization code
            if (isset($_GET['state']) && !isset($_GET['code']) && !isset($_GET['error'])) {
                Log::error('SSO callback missing authorization code and error parameters', [
                    'query_params' => $_GET
                ]);

                throw new SSOAuthenticationException(
                    'Invalid callback from SSO provider. Missing authorization code.',
                    ['query_params' => $_GET]
                );
            }

            Log::info('Starting SSO authentication', [
                'is_callback' => isset($_GET['code']) || isset($_GET['state']),
                'has_code' => isset($_GET['code']),
                'has_state' => isset($_GET['state'])
            ]);

            // Use the simplified authenticate() method from the library
            // This handles both the initial redirect and callback processing
            $this->client->authenticate();

            // Extract user information and permissions from the authenticated session
            $userInfo = $this->extractUserInfo();
            $permissions = $this->extractPermissions();

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('SSO authentication successful', [
                'user_subject' => $userInfo['sub'] ?? 'unknown',
                'user_email' => $userInfo['email'] ?? 'unknown',
                'permissions_count' => count($permissions),
                'processing_time_ms' => $processingTime
            ]);

            return [
                'user_info' => $userInfo,
                'permissions' => $permissions
            ];

        } catch (SSOAuthenticationException $e) {
            // Re-throw SSO authentication exceptions as-is
            throw $e;

        } catch (Exception $e) {
            Log::error('SSO authentication failed with unexpected error', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            // Determine if this is a service availability issue or authentication issue
            if ($this->isServiceUnavailableError($e)) {
                throw new SSOServiceUnavailableException(
                    'SSO service is currently unavailable. Please try again later.',
                    ['original_error' => $e->getMessage()],
                    $e
                );
            } else {
                throw new SSOAuthenticationException(
                    'Authentication failed: ' . $e->getMessage(),
                    ['original_error' => $e->getMessage()],
                    $e
                );
            }
        }
    }



    /**
     * Extract user information using the requestUserInfo method.
     *
     * @return array
     * @throws SSOAuthenticationException
     */
    public function extractUserInfo()
    {
        try {
            // Build user info by requesting individual claims
            $userInfo = [];
            
            // Get the subject (required)
            $sub = $this->client->requestUserInfo('sub');
            if (!$sub) {
                throw new SSOAuthenticationException('Missing required subject (sub) claim');
            }
            $userInfo['sub'] = $sub;

            // Get optional standard claims
            $standardClaims = [
                'name', 'given_name', 'family_name', 'email', 
                'email_verified', 'preferred_username', 'picture', 'updated_at'
            ];

            foreach ($standardClaims as $claim) {
                $value = $this->client->requestUserInfo($claim);
                if ($value !== null) {
                    $userInfo[$claim] = $value;
                }
            }

            Log::debug('Extracted user information using requestUserInfo', [
                'subject' => $userInfo['sub'],
                'has_name' => !empty($userInfo['name']),
                'has_email' => !empty($userInfo['email']),
                'has_username' => !empty($userInfo['preferred_username']),
                'extracted_claims' => array_keys($userInfo)
            ]);

            return $userInfo;

        } catch (Exception $e) {
            Log::error('Failed to extract user information', [
                'error' => $e->getMessage()
            ]);

            if ($e instanceof SSOAuthenticationException) {
                throw $e;
            }

            throw new SSOAuthenticationException(
                'Failed to extract user information: ' . $e->getMessage(),
                ['original_error' => $e->getMessage()],
                $e
            );
        }
    }

    /**
     * Extract permissions from the custom permissions claim.
     *
     * @return array
     * @throws SSOAuthenticationException
     */
    public function extractPermissions()
    {
        try {
            // Get the custom permissions claim using requestUserInfo
            $permissions = $this->client->requestUserInfo('permissions');

            // Handle case where permissions claim is not set or not an array
            if (!is_array($permissions)) {
                Log::warning('Permissions claim is not an array, treating as empty permissions', [
                    'permissions_type' => gettype($permissions),
                    'permissions_value' => is_scalar($permissions) ? $permissions : '[complex type]'
                ]);
                return [];
            }

            // Validate and normalize permission structure
            $validPermissions = [];
            foreach ($permissions as $index => $permission) {
                if ($this->isValidPermissionStructure($permission)) {
                    // Convert to array and ensure consistent structure
                    $permissionArray = (array) $permission;
                    $normalizedPermission = [
                        'id' => $permissionArray['id'],
                        'scope' => $permissionArray['scope'] ?? null
                    ];
                    $validPermissions[] = $normalizedPermission;
                } else {
                    Log::warning('Invalid permission structure found, skipping', [
                        'permission_index' => $index,
                        'permission' => $permission,
                        'expected_structure' => 'object with "id" string field and optional "scope" string/null field'
                    ]);
                }
            }

            Log::info('Extracted permissions using requestUserInfo', [
                'total_permissions' => count($permissions),
                'valid_permissions' => count($validPermissions),
                'permission_ids' => array_column($validPermissions, 'id'),
                'permission_scopes' => array_column($validPermissions, 'scope')
            ]);

            return $validPermissions;

        } catch (Exception $e) {
            Log::error('Failed to extract permissions', [
                'error' => $e->getMessage()
            ]);

            if ($e instanceof SSOAuthenticationException) {
                throw $e;
            }

            throw new SSOAuthenticationException(
                'Failed to extract permissions: ' . $e->getMessage(),
                ['original_error' => $e->getMessage()],
                $e
            );
        }
    }

    /**
     * Sign out the user by clearing local session and redirecting to logout page.
     * Since the OIDC provider doesn't support logout, this only handles local cleanup.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signOut()
    {
        try {
            // Since the OIDC provider doesn't support logout, we only handle local session cleanup
            // The actual session clearing is handled by the AuthController
            
            $redirectUri = config('sso.logout_redirect_uri', '/');
            
            Log::info('Local SSO sign out completed', [
                'redirect_uri' => $redirectUri
            ]);

            return redirect($redirectUri)
                ->with('success', 'Du är nu utloggad från bokningssystemet.');

        } catch (Exception $e) {
            Log::error('Failed to complete sign out process', [
                'error' => $e->getMessage()
            ]);

            // Fallback to home page redirect
            return redirect('/')
                ->with('success', 'Du är nu utloggad från bokningssystemet.');
        }
    }

    /**
     * Get the OpenID Connect client instance.
     *
     * @return OpenIDConnectClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Check if an exception indicates a service unavailability issue.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isServiceUnavailableError(Exception $e)
    {
        $message = strtolower($e->getMessage());
        
        $serviceUnavailableIndicators = [
            'connection refused',
            'connection timeout',
            'network is unreachable',
            'could not resolve host',
            'ssl connection error',
            'curl error',
            'http 5',  // HTTP 5xx errors
            'service unavailable',
            'gateway timeout',
            'bad gateway'
        ];

        foreach ($serviceUnavailableIndicators as $indicator) {
            if (strpos($message, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map OAuth2/OIDC error codes to user-friendly messages.
     *
     * @param string $error
     * @param string $errorDescription
     * @return string
     */
    protected function mapErrorToUserMessage($error, $errorDescription = '')
    {
        switch ($error) {
            case 'access_denied':
                return 'Inloggning avbröts av användaren.';
            
            case 'invalid_request':
                return 'Felaktig inloggningsförfrågan. Kontakta systemadministratören.';
            
            case 'invalid_client':
                return 'Systemet är felkonfigurerat. Kontakta systemadministratören.';
            
            case 'invalid_grant':
                return 'Inloggningsauktorisering är ogiltig eller har gått ut. Försök igen.';
            
            case 'unauthorized_client':
                return 'Systemet har inte behörighet att använda inloggningssystemet. Kontakta systemadministratören.';
            
            case 'unsupported_grant_type':
            case 'unsupported_response_type':
                return 'Inloggningsmetoden stöds inte. Kontakta systemadministratören.';
            
            case 'invalid_scope':
                return 'Begärda behörigheter är ogiltiga. Kontakta systemadministratören.';
            
            case 'server_error':
                return 'Inloggningssystemet har ett internt fel. Försök igen senare.';
            
            case 'temporarily_unavailable':
                return 'Inloggningssystemet är tillfälligt otillgängligt. Försök igen senare.';
            
            default:
                // If we have a description, use it for unknown errors
                if (!empty($errorDescription)) {
                    return 'Inloggning misslyckades: ' . $errorDescription;
                }
                
                return 'Inloggning misslyckades med okänt fel. Kontakta systemadministratören.';
        }
    }

    /**
     * Validate the structure of a permission object.
     *
     * @param mixed $permission
     * @return bool
     */
    protected function isValidPermissionStructure($permission)
    {
        // Must be an array or object
        if (!is_array($permission) && !is_object($permission)) {
            return false;
        }

        // Convert to array for consistent handling
        $permissionArray = (array) $permission;

        // Must have 'id' field
        if (!isset($permissionArray['id']) || !is_string($permissionArray['id'])) {
            return false;
        }

        // 'scope' field is optional but if present should be string or null
        if (isset($permissionArray['scope']) && 
            !is_string($permissionArray['scope']) && 
            $permissionArray['scope'] !== null) {
            return false;
        }

        return true;
    }
}