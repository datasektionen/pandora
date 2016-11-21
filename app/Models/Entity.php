<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;
use Session;

class Entity extends Model
{
    public function events() {
        return $this->hasMany('App\Models\Event');
    }

    public static function forAuthUser() {
    	if (Auth::guest()) {
    		return null;
    	}
    	return Entity::whereIn('pls_group', Session::get('admin', []));
    }
}
