<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;
use Session;

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
     * If user is super admin all entities are given, otherwise only those that is in
     * admin session.
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
        if ($user->isAdmin() || in_array('*', Session::get('manage-entities', []))) {
            return Entity::select('*');
        }
        return Entity::whereIn('hive_scope', Session::get('manage-entities', []));
    }
}
