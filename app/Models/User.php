<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Auth;
use Session;

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

    public function isAdmin() {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }
        return count(Session::get('admin', [])) > 0 && in_array('admin', Session::get('admin', []));
    }

    public function isAdminFor($entity) {
        if (!Auth::check() || Auth::user()->id != $this->id) {
            return false;
        }

        return in_array($entity->pls_group, Session::get('admin', []));
    }

    public function decisionEvents() {
        return Event::select('events.*')
            ->join('entities', 'entities.id', 'events.entity_id')
            ->whereNull('approved')
            ->whereIn('pls_group', Session::get('admin', []))
            ->orderBy('start');
    }
}
