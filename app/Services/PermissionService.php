<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Entity;
use App\Exceptions\SSOPermissionException;
use Exception;

class PermissionService
{
    /**
     * Session key for storing permissions.
     */
    const PERMISSIONS_SESSION_KEY = 'permissions';

    /**
     * Permission types.
     */
    const PERMISSION_MANAGE = 'manage';
    const PERMISSION_ADMIN = 'admin';

    /**
     * Wildcard scope for permissions that apply to all entities.
     */
    const WILDCARD_SCOPE = '*';

    /**
     * Store permissions in the session.
     *
     * @param array $permissions Array of permission objects with 'id' and 'scope' keys
     * @return void
     */
    public function storePermissions(array $permissions)
    {
        // Validate permission structure
        $validatedPermissions = [];
        
        foreach ($permissions as $permission) {
            if ($this->isValidPermission($permission)) {
                $validatedPermissions[] = $permission;
            } else {
                Log::warning('Invalid permission structure ignored', [
                    'permission' => $permission
                ]);
            }
        }

        Session::put(self::PERMISSIONS_SESSION_KEY, $validatedPermissions);
        
        Log::info('Permissions stored in session', [
            'count' => count($validatedPermissions)
        ]);
    }

    /**
     * Check if user has a specific permission with optional scope matching.
     *
     * @param string $permissionId The permission identifier (e.g., 'manage')
     * @param string|null $scope Optional scope to match against (entity name, null for global)
     * @return bool
     */
    public function hasPermission(string $permissionId, $scope = null)
    {
        try {
            $permissions = Session::get(self::PERMISSIONS_SESSION_KEY, []);

            if (empty($permissions)) {
                Log::debug('No permissions found in session for permission check', [
                    'permission_id' => $permissionId,
                    'scope' => $scope
                ]);
                return false;
            }

            foreach ($permissions as $permission) {
                // Check if permission ID matches
                if ($permission['id'] !== $permissionId) {
                    continue;
                }

                // If no scope is required, permission matches
                if ($scope === null) {
                    Log::debug('Permission granted (no scope required)', [
                        'permission_id' => $permissionId,
                        'permission_scope' => $permission['scope'] ?? 'null'
                    ]);
                    return true;
                }

                // Check scope matching
                if ($this->scopeMatches($permission['scope'], $scope)) {
                    Log::debug('Permission granted (scope matched)', [
                        'permission_id' => $permissionId,
                        'required_scope' => $scope,
                        'permission_scope' => $permission['scope'] ?? 'null'
                    ]);
                    return true;
                }
            }

            Log::debug('Permission denied', [
                'permission_id' => $permissionId,
                'required_scope' => $scope,
                'available_permissions' => array_column($permissions, 'id')
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('Error checking permission', [
                'permission_id' => $permissionId,
                'scope' => $scope,
                'error' => $e->getMessage()
            ]);

            // Fail securely - deny permission on error
            return false;
        }
    }

    /**
     * Get entities that the user can access for a specific permission.
     *
     * @param string $permissionId The permission identifier
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEntitiesForPermission(string $permissionId)
    {
        $permissions = Session::get(self::PERMISSIONS_SESSION_KEY, []);
        $entityScopes = [];

        // Collect all scopes for the given permission
        foreach ($permissions as $permission) {
            if ($permission['id'] === $permissionId) {
                $scope = $permission['scope'];
                
                // If wildcard scope, return all entities
                if ($scope === self::WILDCARD_SCOPE) {
                    return Entity::all();
                }
                
                // If null scope, this is a global permission - return all entities
                if ($scope === null) {
                    return Entity::all();
                }
                
                // Add specific entity scope
                if (is_string($scope) && !empty($scope)) {
                    $entityScopes[] = $scope;
                }
            }
        }

        // If no scopes found, return empty collection
        if (empty($entityScopes)) {
            return Entity::whereRaw('1 = 0')->get(); // Empty collection
        }

        // Return entities matching the scopes
        return Entity::whereIn('pls_group', $entityScopes)->get();
    }

    /**
     * Check if the user is a super admin (has admin permission).
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        $result = $this->hasPermission(self::PERMISSION_ADMIN);
        
        Log::debug('Super admin check', [
            'is_super_admin' => $result
        ]);

        return $result;
    }

    /**
     * Check if the user has any admin permissions.
     *
     * @return bool
     */
    public function hasAnyAdminPermission()
    {
        try {
            $permissions = Session::get(self::PERMISSIONS_SESSION_KEY, []);

            if (empty($permissions)) {
                Log::debug('No permissions found for admin check');
                return false;
            }

            $adminPermissions = [];
            foreach ($permissions as $permission) {
                $permissionId = $permission['id'];
                
                if (in_array($permissionId, [
                    self::PERMISSION_MANAGE,
                    self::PERMISSION_ADMIN
                ])) {
                    $adminPermissions[] = $permissionId;
                }
            }

            $hasAdminPermission = !empty($adminPermissions);

            Log::debug('Admin permission check', [
                'has_admin_permission' => $hasAdminPermission,
                'admin_permissions' => $adminPermissions
            ]);

            return $hasAdminPermission;

        } catch (Exception $e) {
            Log::error('Error checking admin permissions', [
                'error' => $e->getMessage()
            ]);

            // Fail securely - deny admin access on error
            return false;
        }
    }

    /**
     * Get all permissions from the session.
     *
     * @return array
     */
    public function getPermissions()
    {
        return Session::get(self::PERMISSIONS_SESSION_KEY, []);
    }

    /**
     * Clear all permissions from the session.
     *
     * @return void
     */
    public function clearPermissions()
    {
        $permissionCount = count(Session::get(self::PERMISSIONS_SESSION_KEY, []));
        
        Session::forget(self::PERMISSIONS_SESSION_KEY);
        
        Log::info('Permissions cleared from session', [
            'cleared_permissions_count' => $permissionCount
        ]);
    }

    /**
     * Validate permissions and log any issues.
     *
     * @param array $permissions
     * @throws SSOPermissionException
     * @return array Valid permissions
     */
    public function validateAndLogPermissions(array $permissions)
    {
        $validPermissions = [];
        $invalidPermissions = [];

        foreach ($permissions as $index => $permission) {
            if ($this->isValidPermission($permission)) {
                $validPermissions[] = $permission;
            } else {
                $invalidPermissions[] = [
                    'index' => $index,
                    'permission' => $permission
                ];
            }
        }

        if (!empty($invalidPermissions)) {
            Log::warning('Invalid permissions found during validation', [
                'invalid_count' => count($invalidPermissions),
                'valid_count' => count($validPermissions),
                'invalid_permissions' => $invalidPermissions
            ]);
        }

        if (empty($validPermissions) && !empty($permissions)) {
            throw new SSOPermissionException('No valid permissions found in provided data', [
                'total_permissions' => count($permissions),
                'invalid_permissions' => $invalidPermissions
            ]);
        }

        Log::info('Permission validation completed', [
            'total_provided' => count($permissions),
            'valid_permissions' => count($validPermissions),
            'invalid_permissions' => count($invalidPermissions)
        ]);

        return $validPermissions;
    }

    /**
     * Validate that a permission has the correct structure.
     *
     * @param mixed $permission The permission to validate
     * @return bool
     */
    protected function isValidPermission($permission)
    {
        // Must be an array or object with 'id' key
        if (!is_array($permission) && !is_object($permission)) {
            return false;
        }

        // Convert object to array for consistent handling
        if (is_object($permission)) {
            $permission = (array) $permission;
        }

        // Must have 'id' key
        if (!isset($permission['id']) || !is_string($permission['id'])) {
            return false;
        }

        // 'scope' key is optional but if present should be string or null
        if (isset($permission['scope']) && 
            !is_string($permission['scope']) && 
            $permission['scope'] !== null) {
            return false;
        }

        return true;
    }

    /**
     * Check if a permission scope matches the required scope.
     *
     * @param string|null $permissionScope The scope from the permission
     * @param string $requiredScope The scope being checked against
     * @return bool
     */
    protected function scopeMatches($permissionScope, string $requiredScope)
    {
        // Wildcard scope matches everything
        if ($permissionScope === self::WILDCARD_SCOPE) {
            return true;
        }

        // Null scope is treated as global permission
        if ($permissionScope === null) {
            return true;
        }

        // Exact scope match
        return $permissionScope === $requiredScope;
    }
}