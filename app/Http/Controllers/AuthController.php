<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Auth;
use Session;
use Jumbojett\OpenIDConnectClient;

use App\Models\User;

/**
 * Authentication controller. Handles login via login.datasektionen.se.
 *
 * @author Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-23
 */
class AuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private OpenIDConnectClient $oidc;

    public function __construct()
    {
        $this->oidc = new OpenIDConnectClient(config('oidc.provider'), config('oidc.client_id'), config('oidc.client_secret'));
        $this->oidc->setRedirectURL(route("oidc-callback"));
        $this->oidc->addScope(["openid", "profile", "permissions"]);
    }

    /**
     * The logout url. Redirects to main page with success message.
     *
     * @return view the welcome view
     */
    public function getLogout()
    {
        Auth::logout();
        Session::forget('admin');
        return redirect('/')
            ->with('success', 'Du är nu utloggad från bokningssystemet.');
    }

    /**
     * The login page. Just redirects to login.
     *
     * @return redirect to login.datasektionen.se
     */
    public function getLogin(Request $request)
    {
        // This either redirects and exits or retrieves the claims if we already have been redirected and got back here.
        $this->oidc->authenticate();
        $claims = $this->oidc->getVerifiedClaims();

        if (!isset($claims->sub)) {
            return redirect('/')
                ->with('error', 'Fick tydligen inte veta vem du är (försök igen eller kontakta d-sys)');
        }

        $user = User::where('kth_username', $claims->sub)->first();

        if ($user === null) {
            $user = new User;
            $user->kth_username = $claims->sub;
        }

        $user->name = $claims->name;
        $user->save();

        Auth::login($user);

        $isAdmin = false;
        $manageEntities = [];
        foreach ($claims->permissions as $perm) {
            if ($perm->id === "admin") $isAdmin = true;
            if ($perm->id === "manage") $manageEntities[] = $perm->scope;
        }

        Session::put('admin', $isAdmin);
        Session::put('manage-entities', $manageEntities);

        return redirect()->intended('/')->with('success', 'Du loggades in.');
    }
}
