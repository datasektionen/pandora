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

use Auth;
use DateTime;
use DB;
use Session;

/**
 * Handles entity requests.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-22
 */
class EntityController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Shows an entity. This includes displaying a week schedule over the activites.
	 * 
	 * @param  int     $id          the id of the entity to show
	 * @param  int     $year        the year that the schedule shows
	 * @param  int     $week        the week that the schedule shows
	 * @param  integer $highlightId the id of the event to highlight
	 * @return a view containing a schedule
	 */
	public function getShow($id, $year = null, $week = null, $highlightId = -1) {
		// Check if entity, year and week exist and set default values
		$entity = Entity::findOrFail($id);
		if ($year === null) { $year = date('Y'); }
		if ($week === null) { $week = date('W'); }

		// Find the starting and ending dates for the week
		$date = new DateTime();
		$date->setISODate($year, $week, "1");
		$date->setTime(0,0,0);
		$startDate = $date->getTimestamp();
		$endDate   = strtotime('+7days', $startDate);

		// We need a nice highlight on today, so lets find which day is today osv.osv.
		if ($endDate < time() || $startDate > time()) {
			$today = -1;
		} else {
			$today = (intval(date('w')) + 6) % 7;
		}

		// Get all bookings for the period
		$query = Event::select('events.*')
			->where('start', '<=', date('Y-m-d H:i:s', $endDate))
			->where('end', '>=', date('Y-m-d H:i:s', $startDate))
			->where('entity_id', $entity->id)
			->orderBy('start', 'DESC');

		// We want to show some less events if we are not admin or owner of the event
		if (!$entity->show_pending_bookings && !in_array($entity->pls_group, Session::get('admin', []))) {
			$query->join('entities', 'entities.id', 'events.entity_id')
				->where(function ($query) {
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

		return view('entity')
			->with('today', $today)
			->with('entity', $entity)
			->with('tracks', $tracks)
			->with('numTracks', $numTracks)
			->with('startDate', $startDate)
			->with('week', $week)
			->with('year', $year)
			->with('nextWeek', date("W", strtotime("+1week", $startDate)))
			->with('nextYear', date("Y", strtotime("+1week", $startDate)))
			->with('prevWeek', date("W", strtotime("-1week", $startDate)))
			->with('prevYear', date("Y", strtotime("-1week", $startDate)))
			->with('highlightId', $highlightId);
	}

	/**
	 * Shows a booking form for entity $id.
	 * 
	 * @param  integer $id entity id
	 * @return booking view
	 */
	public function getBook($id) {
		return view('book')
			->with('entity', Entity::findOrFail($id));
	}

	/**
	 * Handles a post request for booking action.
	 * 
	 * @param  integer  $id      the id of the entity to book
	 * @param  Request  $request the post request
	 * @return redirect to schedule page
	 */
	public function postBook($id, Request $request) {
		$this->validate($request, [
			'startdate' => 'required|date',
			'enddate' => 'required|date',
			'starttime' => [
				'required', 
				'regex:/^(2[0-3]|[0-1][0-9]|[0-9]):([0-5][0-9])$/'
			],
			'endtime' => [
				'required', 
				'regex:/^(2[0-3]|[0-1][0-9]|[0-9]):([0-5][0-9])$/'
			],
			'booker' => 'required',
			'reason' => 'required'
		]);

		// Get the entity
		$entity = Entity::findOrFail($id);

		// Create the event
		$event = new Event;
		$event->start = date("Y-m-d H:i:s", strtotime($request->input('startdate') . ' ' . $request->input('starttime')));
		$event->end = date("Y-m-d H:i:s", strtotime($request->input('enddate') . ' ' . $request->input('endtime')));
		$event->title = $request->input('booker');
		$event->description = $request->input('reason');
		$event->booked_by = Auth::user()->id;
		$event->entity_id = $entity->id;
		$event->alcohol = ($entity->alcohol_question && !$request->has('alcohol')) || ($request->has('alcohol') && $request->input('alcohol') === 'yes');

		// If an admin, the booking is automatically approved, otherwise, notify both admin and user
		if (Auth::check() && Auth::user()->isAdminFor($entity)) {
			$event->approve();
			EmailClient::sendBookingConfirmation($event);
		} else {
			$event->save();
			EmailClient::sendBookingStatus($event);
			EmailClient::sendBookingNotification($event);
		}

		return redirect()->back()->with('success', 'Bokningen sparades');
	}

	/**
	 * Returns an entity's schedule as ical.
	 * 
	 * @param integer $id the id of the entity
	 * @return ical formatted calendar output
	 */
	public function getIcal($id) {
		$entity = Entity::findOrFail($id);

		return response(view('ical.entity')
			->with('events', $entity->events)
			->with('entity', $entity))
            ->withHeaders([
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=' . strtolower($entity->name) . '.ics'
            ]);
	}
}