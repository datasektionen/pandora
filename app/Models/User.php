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
class User extends Authenticatable
{
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
    public function isAdmin()
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }
        return Session::get('admin');
    }

    /**
     * Checks if this user is admin for anything.
     *
     * @return boolean false if user
     *                       - is not logged in or
     *                       - is logged in but is not this user or
     *                       - is logged in and is this user but is not admin for anything (has empty admin session)
     */
    public function isManager()
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }
        return Session::get('admin') || count(Session::get('manage-entities', [])) > 0;
    }

    /**
     * Returns true if this user is admin right now for the given entity.
     *
     * @param Entity $entity the entity to check for
     * @return boolean         false if user is not logged in, or this user
     *                         is not the logged in one, or the user is not admin for the entity
     */
    public function canManage($entity)
    {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        return in_array($entity->hive_scope, Session::get('manage-entities', [])) ||
            in_array('*', Session::get('manage-entities', []));
    }

    /**
     * Returns all events that are not approved or deleted for which the current user can manage.
     *
     * @return Query
     */
    public function decisionEvents()
    {
        $managerFor = Session::get('manage-entities', []);

        $events = Event::select('events.*')
            ->join('entities', 'entities.id', 'events.entity_id')
            ->whereNull('approved')
            ->orderBy('start');

        if (in_array('*', $managerFor) || Session::get('admin'))
            return $events;
        else
            return $events->whereIn('hive_scope', $managerFor);

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
