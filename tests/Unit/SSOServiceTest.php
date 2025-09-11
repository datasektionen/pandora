<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SSOService;
use App\Services\SSOConfigValidator;
use Jumbojett\OpenIDConnectClient;
use App\Exceptions\SSOAuthenticationException;
use Mockery;

class SSOServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the configuration
        config([
            'sso.issuer_url' => 'https://test.example.com',
            'sso.client_id' => 'test_client_id',
            'sso.client_secret' => 'test_client_secret',
            'sso.redirect_uri' => 'https://app.example.com/auth/callback',
            'sso.scopes' => ['openid', 'profile', 'email', 'permissions'],
            'sso.verify_ssl' => false,
            'sso.timeout' => 30,
            'sso.configuration_error' => null
        ]);
    }

    public function testExtractUserInfoWithValidData()
    {
        // Mock the config validator
        $configValidator = Mockery::mock(SSOConfigValidator::class);
        $configValidator->shouldReceive('validate')->once();

        // Mock the OpenID Connect client constructor and methods
        $mockClient = Mockery::mock('overload:' . OpenIDConnectClient::class);
        $mockClient->shouldReceive('setRedirectURL')->once();
        $mockClient->shouldReceive('addScope')->once();
        $mockClient->shouldReceive('setVerifyHost')->once();
        $mockClient->shouldReceive('setVerifyPeer')->once();
        $mockClient->shouldReceive('setTimeout')->once();

        // Mock user info responses
        $mockClient->shouldReceive('requestUserInfo')
            ->with('sub')
            ->andReturn('test_subject_123');
        
        $mockClient->shouldReceive('requestUserInfo')
            ->with('name')
            ->andReturn('Test User');
            
        $mockClient->shouldReceive('requestUserInfo')
            ->with('email')
            ->andReturn('test@example.com');
            
        $mockClient->shouldReceive('requestUserInfo')
            ->with('preferred_username')
            ->andReturn('testuser');

        // Mock other optional claims to return null
        $optionalClaims = ['given_name', 'family_name', 'email_verified', 'picture', 'updated_at'];
        foreach ($optionalClaims as $claim) {
            $mockClient->shouldReceive('requestUserInfo')
                ->with($claim)
                ->andReturn(null);
        }

        // Create service instance
        $service = new SSOService($configValidator);

        // Test extractUserInfo method
        $userInfo = $service->extractUserInfo();

        $this->assertEquals('test_subject_123', $userInfo['sub']);
        $this->assertEquals('Test User', $userInfo['name']);
        $this->assertEquals('test@example.com', $userInfo['email']);
        $this->assertEquals('testuser', $userInfo['preferred_username']);
        $this->assertArrayNotHasKey('given_name', $userInfo); // Should not include null values
    }

    public function testExtractPermissionsWithValidData()
    {
        // Mock the config validator
        $configValidator = Mockery::mock(SSOConfigValidator::class);
        $configValidator->shouldReceive('validate')->once();

        // Mock the OpenID Connect client constructor and methods
        $mockClient = Mockery::mock('overload:' . OpenIDConnectClient::class);
        $mockClient->shouldReceive('setRedirectURL')->once();
        $mockClient->shouldReceive('addScope')->once();
        $mockClient->shouldReceive('setVerifyHost')->once();
        $mockClient->shouldReceive('setVerifyPeer')->once();
        $mockClient->shouldReceive('setTimeout')->once();

        // Mock permissions response
        $mockPermissions = [
            ['id' => 'admin', 'scope' => 'booking_system'],
            ['id' => 'user', 'scope' => null]
        ];
        
        $mockClient->shouldReceive('requestUserInfo')
            ->with('permissions')
            ->andReturn($mockPermissions);

        // Create service instance
        $service = new SSOService($configValidator);

        // Test extractPermissions method
        $permissions = $service->extractPermissions();

        $this->assertCount(2, $permissions);
        $this->assertEquals('admin', $permissions[0]['id']);
        $this->assertEquals('booking_system', $permissions[0]['scope']);
        $this->assertEquals('user', $permissions[1]['id']);
        $this->assertNull($permissions[1]['scope']);
    }

    public function testExtractUserInfoThrowsExceptionWhenSubjectMissing()
    {
        // Mock the config validator
        $configValidator = Mockery::mock(SSOConfigValidator::class);
        $configValidator->shouldReceive('validate')->once();

        // Mock the OpenID Connect client constructor and methods
        $mockClient = Mockery::mock('overload:' . OpenIDConnectClient::class);
        $mockClient->shouldReceive('setRedirectURL')->once();
        $mockClient->shouldReceive('addScope')->once();
        $mockClient->shouldReceive('setVerifyHost')->once();
        $mockClient->shouldReceive('setVerifyPeer')->once();
        $mockClient->shouldReceive('setTimeout')->once();

        // Mock missing subject
        $mockClient->shouldReceive('requestUserInfo')
            ->with('sub')
            ->andReturn(null);

        // Create service instance
        $service = new SSOService($configValidator);

        // Test that exception is thrown
        $this->expectException(SSOAuthenticationException::class);
        $this->expectExceptionMessage('Missing required subject (sub) claim');
        
        $service->extractUserInfo();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}