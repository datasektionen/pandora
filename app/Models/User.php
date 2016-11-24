<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Auth;
use Session;

/**
 * A class defining a user. With kth_username and so on.
 *
 * @author Jonas Dahl <jonadahl@kth.se>
 * @version 2016-11-22
 */
class User extends Authenticatable {
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
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
     *                         (ie has 'admin' in Session admin array)
     */
    public function isAdmin() {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }
        return count(Session::get('admin', [])) > 0 && in_array('admin', Session::get('admin', []));
    }

    /**
     * Checks if this user is admin for anything.
     * 
     * @return boolean false if user 
     *                       - is not logged in or
     *                       - is logged in but is not this user or
     *                       - is logged in and is this user but is not admin for anything (has empty admin session)
     */
    public function isSomeAdmin() {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }
        return count(Session::get('admin', [])) > 0;
    }

    /**
     * Returns true if this user is admin right now for the given entity.
     * 
     * @param  Entity  $entity the entity to check for
     * @return boolean         false if user is not logged in, or this user 
     *                         is not the logged in one, or the user is not admin for the entity
     */
    public function isAdminFor($entity) {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        return in_array($entity->pls_group, Session::get('admin', []));
    }

    /**
     * Returns all events that are not approved or deleted for the current user as admin.
     * 
     * @return Query
     */
    public function decisionEvents() {
        return Event::select('events.*')
            ->join('entities', 'entities.id', 'events.entity_id')
            ->whereNull('approved')
            ->whereIn('pls_group', Session::get('admin', []))
            ->orderBy('start');
    }
}
