<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;
use Session;
use App\Services\PermissionService;

/**
 * An entity can be a car or a house or a room or anything.
 *
 * @author Jonas Dahl <jonadahl@kth.se>
 * @version 2016-11-22
 */
class Entity extends Model
{
    /**
     * Defines relation to all the entity's events.
     *
     * @return relation
     */
    public function events()
    {
        return $this->hasMany('App\Models\Event');
    }

    /**
     * Defines relation to a parent.
     * For example, Mötesrummet can be part of Meta.
     *
     * @return relation
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Entity', 'part_of');
    }

    /**
     * Returns all entities for the given user. Auth::user() is used if none given.
     * If user is super admin all entities are given, otherwise only those that the user
     * has manage permissions for.
     *
     * @return null no user given or a Query otherwise
     */
    public static function forAuthUser($user = null)
    {
        if ($user == null) {
            $user = Auth::user();
        }
        if ($user == null) {
            return null;
        }

        $permissionService = app(PermissionService::class);

        // If user is super admin (has admin permission), return all entities
        if ($permissionService->isSuperAdmin()) {
            return Entity::select('*');
        }

        // Get entity scopes the user can access based on permissions
        $accessibleEntityScopes = [];
        $permissions = $permissionService->getPermissions();

        foreach ($permissions as $permission) {
            $permissionId = $permission['id'];
            $scope = $permission['scope'];

            // Only consider manage permissions
            if ($permissionId !== PermissionService::PERMISSION_MANAGE) {
                continue;
            }

            // Handle wildcard scope - user can access all entities
            if ($scope === PermissionService::WILDCARD_SCOPE) {
                return Entity::select('*');
            }

            // Add specific entity scope
            if (is_string($scope) && !empty($scope)) {
                $accessibleEntityScopes[] = $scope;
            }
        }

        // Remove duplicates
        $accessibleEntityScopes = array_unique($accessibleEntityScopes);

        // If no accessible entities, return empty query
        if (empty($accessibleEntityScopes)) {
            return Entity::whereRaw('1 = 0'); // Returns empty query
        }

        // Return entities the user can access
        return Entity::whereIn('pls_group', $accessibleEntityScopes);
    }
}
