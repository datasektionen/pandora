<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers;
use Illuminate\Http\Request;


use \App\Helpers\EmailClient;
use \App\Models\Event;
use \App\Models\User;
use \App\Models\Entity;
use Auth;
use DateTime;
use DB;
use Session;

/**
 * Handles administrator actions concerning elections.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-10-14
 */
class EntityController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function getShow($id, $year = null, $week = null) {
		$entity = Entity::find($id);
		if ($entity === null) {
			return redirect()->back();
		}
		if ($year === null) {
			$year = date('Y');
		}
		if ($week === null) {
			$week = date('W');
		}
		$date = new DateTime();
		$date->setISODate($year, $week, "1");
		$date->setTime(0,0,0);
		$startDate = $date->getTimestamp();
		$endDate   = strtotime('+7days', $startDate);
		if ($endDate < time() || $startDate > time()) {
			$today = -1;
		} else {
			$today = (intval(date('w')) + 6) % 7;
		}

		$prevWeek = date("W", strtotime("-1week", $startDate));
		$prevYear = date("Y", strtotime("-1week", $startDate));
		$nextWeek = date("W", strtotime("+1week", $startDate));
		$nextYear = date("Y", strtotime("+1week", $startDate));

		// Define the collision function
		$collidesWithTrack = function($track, $booking) {
			$bookingStart = strtotime($booking->start);
			$bookingEnd = strtotime($booking->end);
			foreach ($track as $element) {
				$elementStart = strtotime($element->start);
				$elementEnd = strtotime($element->end);

				if ($elementStart <= $bookingStart && $elementEnd > $bookingStart)
					return true;
				if ($bookingStart <= $elementStart && $bookingEnd > $elementStart)
					return true;
			}
			return false;
		};

		// Get all bookings for the period
		$query = Event::select('events.*')
			->where(function ($query) use ($startDate, $endDate) {
				$query->where('start', '>=', date('Y-m-d H:i:s', $startDate))
					->orWhere('end', '<=', date('Y-m-d H:i:s', $endDate));
			})
			->where('entity_id', $entity->id)
			->orderBy(DB::raw('end-start'), 'DESC');

		if (!$entity->show_pending_bookings && !in_array($entity->pls_group, Session::get('admin', []))) {
			$query->join('entities', 'entities.id', 'events.entity_id')
				->where(function ($query) {
				$query
					->whereNotNull('approved')
					->orWhere('show_pending_bookings', true);
				if (Auth::check())
					$query->orWhere('created_by', Auth::user()->id);
			});
		}

		$ans = $query->get();
		$res = [];
		//echo "Start<br>";
		foreach ($ans as $booking) {
			//echo "Tittar på " . $booking->title . " (".$booking->start." till ".$booking->end.")<br>";
			while (date("Y-m-d", strtotime($booking->start)) != date("Y-m-d", strtotime($booking->end) - 1) && 
				strtotime($booking->start) < strtotime($booking->end)) {
				$newBooking = clone $booking;
				$newBooking->end = $booking->end;
				$newBooking->start = date("Y-m-d", strtotime($booking->end) - 1) . ' 00:00:00';
				$booking->end = date("Y-m-d", strtotime($booking->end) - 1) . ' 00:00:00';
				if (strtotime($newBooking->end) <= $endDate && strtotime($newBooking->start) >= $startDate)
					$res[] = $newBooking;
				//echo " &nbsp;  &nbsp;  &nbsp; Skapade ny bokning: " . $newBooking->title . " (".$newBooking->start." till ".$newBooking->end.")<br>";
			}
			$res[] = $booking;
			//echo " &nbsp;  &nbsp;  &nbsp; La till bokning: " . $booking->title . " (".$booking->start." till ".$booking->end.")<br>";
		}
		$bookings = collect($res);

		$tracks = [];
		$numTracks = [];
		foreach ($bookings as $booking) {
			$booking->colspan = 1;
			$found = false;

			$date = date("Y-m-d", strtotime($booking->start));
			if (!isset($numTracks[$date]))
				$numTracks[$date] = 0;

			for ($i = 0; $i < $numTracks[$date]; $i++) {
				if (!$collidesWithTrack($tracks[$date][$i], $booking)) {
					$tracks[$date][$i][] = $booking;
					$found = true;
					break;
				}
			}
			if (!$found) {
				$numTracks[$date]++;
				$tracks[$date][$numTracks[$date]-1][] = $booking;
			}
		}

		foreach ($tracks as $date => $dayTracks) {
			foreach ($dayTracks as $key => $track) {
				foreach ($track as $booking) {
					for ($i = $key + 1; $i < count($tracks[$date]); $i++) {
						if (!$collidesWithTrack($tracks[$date][$i], $booking)) {
							$booking->colspan++;
						} else {
							break;
						}
					}
				}
			}
		}

		$entity = Entity::find($id);

		return view('entity')
		->with('today', $today)
		->with('entity', $entity)
		->with('tracks', $tracks)
		->with('numTracks', $numTracks)
		->with('startDate', $startDate)
		->with('week', $week)
		->with('year', $year)
		->with('nextWeek', $nextWeek)
		->with('nextYear', $nextYear)
		->with('prevWeek', $prevWeek)
		->with('prevYear', $nextYear);
	}

	public function getBook($id) {
		$entity = Entity::find($id);
		if ($entity === null) {
			return redirect()->back()->with('error', 'Kunde inte hittat entiteten.');
		}
		return view('book')
			->with('entity', $entity);
	}

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
		// TODO redirect to defined page, not back!
		$entity = Entity::find($id);
		if ($entity === null) {
			return redirect()->back()->with('error', 'Kunde inte hitta entiteten.');
		}

		$event = new Event;
		$event->start = date("Y-m-d H:i:s", strtotime($request->input('startdate') . ' ' . $request->input('starttime')));
		$event->end = date("Y-m-d H:i:s", strtotime($request->input('enddate') . ' ' . $request->input('endtime')));
		$event->title = $request->input('booker');
		$event->description = $request->input('reason');
		$event->booked_by = 1;
		$event->entity_id = $entity->id;
		$event->alcohol = ($entity->alcohol_question && !$request->has('alcohol')) || ($request->has('alcohol') && $request->input('alcohol') === 'yes');
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

	public function getIcal($id) {
		$entity = Entity::find($id);
		if ($entity === null) {
			abort(404);
		}

		return response(view('ical.entity')
			->with('events', $entity->events)
			->with('entity', $entity))
            ->withHeaders([
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=' . strtolower($entity->name) . '.ics'
            ]);
	}
}