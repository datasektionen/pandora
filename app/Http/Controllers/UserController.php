<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Helpers\EmailClient;
use App\Helpers\Planner;
use App\Models\Event;
use App\Models\User;
use App\Models\Entity;
use App\Http\Controllers;

use Validator;
use Auth;
use DateTime;
use DB;
use Session;
use Carbon\Carbon;

/**
 * Handles entity requests.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-22
 */
class UserController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Returns an entity's schedule as ical.
     *
     * @param integer $id the id of the entity
     * @return ical formatted calendar output
     */
    public function getIndex()
    {
        $user = Auth::user();

        return view('user.index')
            ->with('past', $user->bookings()->where('end', '<', Carbon::now())->orderBy('start', 'DESC')->get())
            ->with('future', $user->bookings()->where('end', '>', Carbon::now())->orderBy('start', 'ASC')->get())
            ->with('user', $user);
    }
}
