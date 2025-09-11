<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Auth;
use App\Services\PermissionService;
use App\Models\Event;

/**
 * A class defining a user. With kth_username and so on.
 *
 * @author Jonas Dahl <jonadahl@kth.se>
 * @version 2016-11-22
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'kth_username',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Checks if this user is admin right now on this session.
     *
     * @return boolean false if user
     *                       - is not logged in or
     *                       - is logged in but is not this user or
     *                       - is logged in and is this user but is not super admin
     *                         (ie has 'admin' permission)
     */
    public function isAdmin()
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        $permissionService = app(PermissionService::class);
        return $permissionService->isSuperAdmin();
    }

    /**
     * Checks if this user is admin for anything.
     *
     * @return boolean false if user
     *                       - is not logged in or
     *                       - is logged in but is not this user or
     *                       - is logged in and is this user but is not admin for anything (has no admin permissions)
     */
    public function isSomeAdmin()
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        $permissionService = app(PermissionService::class);
        return $permissionService->hasAnyAdminPermission();
    }

    /**
     * Returns true if this user is admin right now for the given entity.
     *
     * @param Entity $entity the entity to check for
     * @return boolean         false if user is not logged in, or this user
     *                         is not the logged in one, or the user is not admin for the entity
     */
    public function isAdminFor($entity)
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        $permissionService = app(PermissionService::class);
        return $permissionService->hasPermission(PermissionService::PERMISSION_MANAGE, $entity->pls_group);
    }

    /**
     * Returns true if this user can see bookings for the given entity.
     * Note: This now uses the 'manage' permission since see-bookings and manage-bookings have been merged.
     *
     * @param Entity $entity the entity to check for
     * @return boolean         false if user is not logged in, or this user
     *                         is not the logged in one, or the user cannot see bookings for the entity
     */
    public function canSeeBookings($entity)
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        $permissionService = app(PermissionService::class);
        return $permissionService->hasPermission(PermissionService::PERMISSION_MANAGE, $entity->pls_group);
    }

    /**
     * Returns all events that are not approved or deleted for the current user as admin.
     *
     * @return Query
     */
    public function decisionEvents()
    {
        $permissionService = app(PermissionService::class);
        $entities = $permissionService->getEntitiesForPermission(PermissionService::PERMISSION_MANAGE);

        // Extract pls_group values from the entities collection
        $plsGroups = $entities->pluck('pls_group')->toArray();

        // If no entities accessible, return empty query
        if (empty($plsGroups)) {
            return Event::whereRaw('1 = 0');
        }

        return Event::select('events.*')
            ->join('entities', 'entities.id', 'events.entity_id')
            ->whereNull('approved')
            ->whereIn('pls_group', $plsGroups)
            ->orderBy('start');
    }

    /**
     * Returns all bookings for user.
     *
     * @return query
     */
    public function bookings()
    {
        return $this->hasMany('App\Models\Event', 'booked_by');
    }
}
