<?php

use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Event;
use App\Helpers\Planner;
use App\Services\PermissionService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('events/{id}/{year?}/{week?}', function ($id, $year = null, $week = null) {
    // Check if entity, year and week exist and set default values
    $entity = Entity::findOrFail($id);
    if ($year === null) {
        $year = date('Y');
    }
    if ($week === null) {
        $week = date('W');
    }

    // Find the starting and ending dates for the week
    $date = new DateTime();
    $date->setISODate($year, $week, "1");
    $date->setTime(0, 0, 0);
    $startDate = $date->getTimestamp();
    $endDate = strtotime('+7days', $startDate);

    // We need a nice highlight on today, so lets find which day is today osv.osv.
    if ($endDate < time() || $startDate > time()) {
        $today = -1;
    } else {
        $today = (intval(date('w')) + 6) % 7;
    }

    // Get all bookings for the period
    $query = Event::select('events.id', 'title', 'events.description', 'entity_id', 'start', 'end')
        ->where('start', '<=', date('Y-m-d H:i:s', $endDate))
        ->where('end', '>=', date('Y-m-d H:i:s', $startDate))
        ->join('entities', 'entities.id', 'events.entity_id')
        ->where(function ($query) use ($entity) {
            $query->where('entity_id', $entity->id)
                ->orWhere('entity_id', $entity->part_of);
        })
        ->orderBy('start', 'DESC');

    // We want to show some less events if we are not admin or owner of the event
    $permissionService = app(PermissionService::class);
    $canManageBookings = Auth::check() && $permissionService->hasPermission(PermissionService::PERMISSION_MANAGE, $entity->pls_group);
    
    if (!$entity->show_pending_bookings && !$canManageBookings) {
        $query->where(function ($query) {
            $query
                ->whereNotNull('approved')
                ->orWhere('show_pending_bookings', true);
            if (Auth::check())
                $query->orWhere('booked_by', Auth::user()->id);
        });
    }

    $ans = $query->get();

    // Do the actual planning of events
    $planner = new Planner($startDate, $endDate, $ans);
    list($tracks, $numTracks) = $planner->planEvents();

    $response = new \stdClass;
    $response->tracks = [];
    foreach ($tracks as $date => $content) {
        $a = new \stdClass;
        $a->date = new \stdClass;
        $a->date->yyyymmdd = $date;
        $a->date->jn = date("j/n", strtotime($date));
        $a->events = $content;
        $response->tracks[] = $a;

        foreach ($a->events as &$val) {
            foreach ($val as &$m) {
                $m->numtracks = $numTracks[$date];
                $m->startHi = date("H:i", strtotime($m->start));
                $m->endHi = date("H:i", strtotime($m->end));
            }
        }
    }

    $response->startDate = date("Y-m-d", $startDate);
    $response->endDate = date("Y-m-d", $endDate);
    $response->week = date("W", $startDate);
    $response->year = date("Y", $startDate);

    return response()->json($response)->header('Access-Control-Allow-Origin', '*');
});
