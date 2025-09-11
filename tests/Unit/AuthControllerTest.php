<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AuthController;
use App\Services\SSOService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testHandleCallbackWithOAuthErrorsPreventsInfiniteLoop()
    {
        // Mock the services
        $ssoService = Mockery::mock(SSOService::class);
        $permissionService = Mockery::mock(PermissionService::class);
        
        // Mock clearing permissions when error occurs
        $permissionService->shouldReceive('clearPermissions')->once();

        // Create controller instance
        $controller = new AuthController($ssoService, $permissionService);

        // Create request with OAuth error parameters (simulating SSO provider callback with error)
        $request = Request::create('/auth/callback', 'GET', [
            'error' => 'invalid_client',
            'error_description' => 'Client authentication failed',
            'state' => 'some_state_value'
        ]);

        // Mock Auth facade
        Auth::shouldReceive('logout')->once();

        // Call handleCallback method
        $response = $controller->handleCallback($request);

        // Verify it redirects to home page (not back to login) with error message
        $this->assertEquals(302, $response->getStatusCode());
        $path = parse_url($response->getTargetUrl(), PHP_URL_PATH);
        $this->assertTrue(
            $path === '/' || $path === '' || $path === null,
            'Expected redirect to home page (path: / or empty), got path: ' . $path
        );
        
        // Verify error message is set in session
        $this->assertEquals(
            'Systemet är felkonfigurerat. Kontakta systemadministratören.',
            $response->getSession()->get('error')
        );
    }

    public function testHandleCallbackWithAccessDeniedError()
    {
        // Mock the services
        $ssoService = Mockery::mock(SSOService::class);
        $permissionService = Mockery::mock(PermissionService::class);
        
        // Mock clearing permissions when error occurs
        $permissionService->shouldReceive('clearPermissions')->once();

        // Create controller instance
        $controller = new AuthController($ssoService, $permissionService);

        // Create request with access denied error from callback
        $request = Request::create('/auth/callback', 'GET', [
            'error' => 'access_denied',
            'error_description' => 'The user denied the request',
            'state' => 'some_state_value'
        ]);

        // Mock Auth facade
        Auth::shouldReceive('logout')->once();

        // Call handleCallback method
        $response = $controller->handleCallback($request);

        // Verify it redirects to home page with appropriate message
        $this->assertEquals(302, $response->getStatusCode());
        $path = parse_url($response->getTargetUrl(), PHP_URL_PATH);
        $this->assertTrue(
            $path === '/' || $path === '' || $path === null,
            'Expected redirect to home page (path: / or empty), got path: ' . $path
        );
        
        // Verify user-friendly error message
        $this->assertEquals(
            'Inloggning avbröts av användaren.',
            $response->getSession()->get('error')
        );
    }

    public function testHandleCallbackWithoutAuthorizationCode()
    {
        // Mock the services
        $ssoService = Mockery::mock(SSOService::class);
        $permissionService = Mockery::mock(PermissionService::class);
        
        // Mock clearing permissions when error occurs
        $permissionService->shouldReceive('clearPermissions')->once();

        // Create controller instance
        $controller = new AuthController($ssoService, $permissionService);

        // Create request with state but no code (invalid callback)
        $request = Request::create('/auth/callback', 'GET', [
            'state' => 'some_state_value'
            // Missing 'code' parameter
        ]);

        // Mock Auth facade
        Auth::shouldReceive('logout')->once();

        // Call handleCallback method
        $response = $controller->handleCallback($request);

        // Verify it redirects to home page (preventing infinite loop)
        $this->assertEquals(302, $response->getStatusCode());
        $path = parse_url($response->getTargetUrl(), PHP_URL_PATH);
        $this->assertTrue(
            $path === '/' || $path === '' || $path === null,
            'Expected redirect to home page (path: / or empty), got path: ' . $path
        );
        
        // Verify specific error message for missing code
        $this->assertEquals(
            'Ogiltig återkoppling från inloggningssystemet. Försök igen.',
            $response->getSession()->get('error')
        );
    }

    public function testHandleCallbackWithUnknownErrorAndDescription()
    {
        // Mock the services
        $ssoService = Mockery::mock(SSOService::class);
        $permissionService = Mockery::mock(PermissionService::class);
        
        // Mock clearing permissions when error occurs
        $permissionService->shouldReceive('clearPermissions')->once();

        // Create controller instance
        $controller = new AuthController($ssoService, $permissionService);

        // Create request with unknown error but with description from callback
        $request = Request::create('/auth/callback', 'GET', [
            'error' => 'custom_error',
            'error_description' => 'Something went wrong on the provider side',
            'state' => 'some_state_value'
        ]);

        // Mock Auth facade
        Auth::shouldReceive('logout')->once();

        // Call handleCallback method
        $response = $controller->handleCallback($request);

        // Verify it redirects to home page
        $this->assertEquals(302, $response->getStatusCode());
        $path = parse_url($response->getTargetUrl(), PHP_URL_PATH);
        $this->assertTrue(
            $path === '/' || $path === '' || $path === null,
            'Expected redirect to home page (path: / or empty), got path: ' . $path
        );
        
        // Verify error description is included in message
        $this->assertEquals(
            'Inloggning misslyckades: Something went wrong on the provider side',
            $response->getSession()->get('error')
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}