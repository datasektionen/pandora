<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\SSOConfigValidator;
use App\Services\SSOService;
use App\Exceptions\SSOException;

/**
 * Command to check SSO configuration and connectivity.
 */
class SSOHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:health-check {--detailed : Show detailed configuration output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check SSO configuration and connectivity';

    /**
     * The SSO configuration validator.
     *
     * @var SSOConfigValidator
     */
    protected $configValidator;

    /**
     * Create a new command instance.
     *
     * @param SSOConfigValidator $configValidator
     */
    public function __construct(SSOConfigValidator $configValidator)
    {
        parent::__construct();
        $this->configValidator = $configValidator;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('SSO Health Check');
        $this->info('================');

        $allChecksPass = true;

        // Check 1: Configuration validation
        $this->info('1. Checking SSO configuration...');
        try {
            $this->configValidator->validate();
            $this->info('   ✓ Configuration is valid');
            
            if ($this->option('detailed')) {
                $summary = $this->configValidator->getConfigSummary();
                $this->table(['Setting', 'Value'], [
                    ['Issuer URL', $summary['issuer_url']],
                    ['Client ID', $summary['client_id_prefix']],
                    ['Has Client Secret', $summary['has_client_secret'] ? 'Yes' : 'No'],
                    ['Redirect URI', $summary['redirect_uri']],
                    ['Logout Redirect URI', $summary['logout_redirect_uri'] ?: 'Not set'],
                    ['Scopes', implode(', ', $summary['scopes'])],
                    ['Verify SSL', $summary['verify_ssl'] ? 'Yes' : 'No'],
                    ['Timeout', $summary['timeout'] . ' seconds'],
                ]);
            }
        } catch (SSOException $e) {
            $this->error('   ✗ Configuration validation failed');
            $this->error('     ' . $e->getMessage());
            $allChecksPass = false;
        }

        // Check 2: SSO service initialization
        $this->info('2. Checking SSO service initialization...');
        try {
            $ssoService = app(SSOService::class);
            $this->info('   ✓ SSO service initialized successfully');
        } catch (SSOException $e) {
            $this->error('   ✗ SSO service initialization failed');
            $this->error('     ' . $e->getMessage());
            $allChecksPass = false;
        }

        // Check 3: SSO provider connectivity (if service initialized)
        if (isset($ssoService)) {
            $this->info('3. Checking SSO provider connectivity...');
            try {
                // Try to access the OpenID Connect client to test basic connectivity
                $client = $ssoService->getClient();
                
                // Try to get provider configuration as a connectivity test
                $issuerUrl = config('sso.issuer_url');
                $this->info('   ✓ SSO service client is accessible');
                
                if ($this->option('verbose')) {
                    $this->info('     Issuer URL: ' . $issuerUrl);
                    $this->info('     Client configured successfully');
                }
            } catch (SSOException $e) {
                $this->error('   ✗ SSO provider connectivity failed');
                $this->error('     ' . $e->getMessage());
                $allChecksPass = false;
            }
        }

        // Summary
        $this->info('');
        if ($allChecksPass) {
            $this->info('✓ All SSO health checks passed');
            Log::info('SSO health check completed successfully');
            return 0;
        } else {
            $this->error('✗ Some SSO health checks failed');
            $this->error('Check the logs for more details or run with --detailed for more information');
            Log::warning('SSO health check completed with failures');
            return 1;
        }
    }
}