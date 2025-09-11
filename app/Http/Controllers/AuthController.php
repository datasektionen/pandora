<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\SSOService;
use App\Services\PermissionService;
use App\Models\User;
use App\Exceptions\SSOException;
use App\Exceptions\SSOConfigurationException;
use App\Exceptions\SSOAuthenticationException;
use App\Exceptions\SSOServiceUnavailableException;
use Exception;

/**
 * Authentication controller. Handles login via SSO using OpenID Connect.
 *
 * @author Jonas Dahl <jonas@jdahl.se>
 * @version 2024-09-09
 */
class AuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * The SSO service instance.
     *
     * @var SSOService
     */
    protected $ssoService;

    /**
     * The permission service instance.
     *
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * Create a new controller instance.
     *
     * @param SSOService $ssoService
     * @param PermissionService $permissionService
     */
    public function __construct(SSOService $ssoService, PermissionService $permissionService)
    {
        $this->ssoService = $ssoService;
        $this->permissionService = $permissionService;
    }

    /**
     * Initiate SSO login by redirecting to the authorization server.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiateLogin(Request $request)
    {
        try {
            Log::info('Initiating SSO login', [
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'intended_url' => $request->session()->get('url.intended')
            ]);

            // Use the simplified authenticate method which will redirect to SSO provider
            $this->ssoService->authenticate();

            // This line should not be reached as authenticate() redirects
            return redirect('/');

        } catch (SSOConfigurationException $e) {
            Log::critical('SSO configuration error during login initiation', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
                'user_ip' => $request->ip()
            ]);

            return redirect('/')
                ->with('error', 'Inloggningssystemet är felkonfigurerat. Kontakta systemadministratören.');

        } catch (SSOServiceUnavailableException $e) {
            Log::error('SSO service unavailable during login initiation', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
                'user_ip' => $request->ip()
            ]);

            return redirect('/')
                ->with('error', 'Inloggningssystemet är för närvarande otillgängligt. Försök igen senare.');

        } catch (Exception $e) {
            Log::error('Unexpected error during SSO login initiation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_ip' => $request->ip()
            ]);

            return redirect('/')
                ->with('error', 'Ett oväntat fel uppstod under inloggningen. Försök igen.');
        }
    }

    /**
     * Handle the SSO callback from the authorization server.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCallback(Request $request)
    {
        $startTime = microtime(true);

        try {
            // Check if the callback contains an error from the SSO provider
            if ($request->has('error')) {
                Log::warning('SSO callback contains error from provider', [
                    'error' => $request->get('error'),
                    'error_description' => $request->get('error_description'),
                    'error_uri' => $request->get('error_uri'),
                    'user_ip' => $request->ip()
                ]);

                // Clear any authentication state
                $this->clearAuthenticationState();

                // Redirect to home page with user-friendly error message
                $errorMessage = $this->getErrorMessageFromRequest($request);
                return redirect('/')
                    ->with('error', $errorMessage);
            }

            // Check if callback is missing required authorization code
            if (!$request->has('code')) {
                Log::error('SSO callback missing authorization code', [
                    'query_params' => $request->query(),
                    'user_ip' => $request->ip()
                ]);

                $this->clearAuthenticationState();

                return redirect('/')
                    ->with('error', 'Ogiltig återkoppling från inloggningssystemet. Försök igen.');
            }

            Log::info('Processing SSO callback', [
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'intended_url' => $request->session()->get('url.intended'),
                'has_code' => $request->has('code'),
                'has_state' => $request->has('state')
            ]);

            // Process the callback using the SSO service
            $authData = $this->ssoService->authenticate();
            $userInfo = $authData['user_info'];
            $permissions = $authData['permissions'];

            // Validate and store permissions
            $validPermissions = $this->permissionService->validateAndLogPermissions($permissions);
            $this->permissionService->storePermissions($validPermissions);

            // Find or create user
            $user = $this->findOrCreateUser($userInfo);

            // Log the user in
            Auth::login($user);

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('User successfully authenticated via SSO callback', [
                'user_id' => $user->id,
                'sso_subject' => $user->sso_subject,
                'user_email' => $user->email,
                'permissions_count' => count($validPermissions),
                'processing_time_ms' => $processingTime,
                'user_ip' => $request->ip()
            ]);

            return redirect()->intended('/')
                ->with('success', 'Du loggades in.');

        } catch (SSOConfigurationException $e) {
            Log::critical('SSO configuration error during callback processing', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
                'user_ip' => $request->ip()
            ]);

            return redirect('/')
                ->with('error', 'Inloggningssystemet är felkonfigurerat. Kontakta systemadministratören.');

        } catch (SSOAuthenticationException $e) {
            Log::error('SSO authentication failed during callback', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
                'user_ip' => $request->ip(),
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            $this->clearAuthenticationState();

            // Redirect to home page instead of login to prevent infinite loops
            return redirect('/')
                ->with('error', $e->getMessage());

        } catch (SSOServiceUnavailableException $e) {
            Log::error('SSO service unavailable during callback', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
                'user_ip' => $request->ip(),
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            $this->clearAuthenticationState();

            return redirect('/')
                ->with('error', 'Inloggningssystemet är för närvarande otillgängligt. Försök igen senare.');

        } catch (Exception $e) {
            Log::error('Unexpected error during SSO callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_ip' => $request->ip(),
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            $this->clearAuthenticationState();

            return redirect('/')
                ->with('error', 'Ett oväntat fel uppstod under inloggningen. Försök igen.');
        }
    }

    /**
     * The logout url. Clears session and redirects to SSO logout endpoint.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLogout(Request $request)
    {
        $userId = Auth::id();
        $userEmail = Auth::user() ? Auth::user()->email : 'unknown';

        try {
            // Clear local authentication and permissions
            Auth::logout();
            $this->permissionService->clearPermissions();
            
            // Clear legacy admin session data if it exists
            Session::forget('admin');
            
            // Invalidate the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->ssoService->signOut();
        } catch (SSOServiceUnavailableException $e) {
            Log::warning('SSO logout unavailable, performing local logout only', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'error' => $e->getMessage(),
                'user_ip' => $request->ip()
            ]);

            return redirect('/')
                ->with('success', 'Du är nu utloggad från bokningssystemet.');

        } catch (Exception $e) {
            Log::error('Logout failed with unexpected error', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'error' => $e->getMessage(),
                'user_ip' => $request->ip()
            ]);

            // Ensure user is logged out locally even if SSO logout fails
            return redirect('/')
                ->with('success', 'Du är nu utloggad från bokningssystemet.');
        }
    }

    /**
     * Find or create a user based on SSO user information.
     *
     * @param array $userInfo
     * @return User
     */
    protected function findOrCreateUser(array $userInfo)
    {
        $username = $userInfo['sub'];

        $user = User::where('kth_username', $username)->first();

        if ($user) {
            // Update user information if needed
            $this->updateUserInfo($user, $userInfo);
            return $user;
        }

        // Create new user
        $user = new User();
        $user->kth_username = $username;
        $this->updateUserInfo($user, $userInfo);

        return $user;
    }

    /**
     * Update user information from SSO data.
     *
     * @param User $user
     * @param array $userInfo
     * @return void
     */
    protected function updateUserInfo(User $user, array $userInfo)
    {
        $changes = [];

        if (!empty($userInfo['name']) && $user->name !== $userInfo['name']) {
            $changes['name'] = ['from' => $user->name, 'to' => $userInfo['name']];
            $user->name = $userInfo['name'];
        }

        if (!empty($changes)) {
            $user->save();

            Log::info('User information updated from SSO', [
                'user_id' => $user->id,
                'sso_subject' => $user->sso_subject,
                'changes' => $changes
            ]);
        } else {
            Log::debug('No user information changes needed', [
                'user_id' => $user->id,
                'sso_subject' => $user->sso_subject
            ]);
        }
    }

    /**
     * Extract error message from SSO callback request.
     *
     * @param Request $request
     * @return string
     */
    protected function getErrorMessageFromRequest(Request $request)
    {
        $error = $request->get('error');
        $errorDescription = $request->get('error_description', '');

        // Map common OAuth2/OIDC errors to user-friendly messages
        switch ($error) {
            case 'access_denied':
                return 'Inloggning avbröts av användaren.';
            
            case 'invalid_request':
                return 'Felaktig inloggningsförfrågan. Kontakta systemadministratören.';
            
            case 'invalid_client':
                return 'Systemet är felkonfigurerat. Kontakta systemadministratören.';
            
            case 'unauthorized_client':
                return 'Systemet har inte behörighet att använda inloggningssystemet. Kontakta systemadministratören.';
            
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
                
                return 'Inloggning misslyckades. Kontakta systemadministratören om problemet kvarstår.';
        }
    }

    /**
     * Clear authentication state on error.
     *
     * @return void
     */
    protected function clearAuthenticationState()
    {
        Auth::logout();
        $this->permissionService->clearPermissions();
    }
}
