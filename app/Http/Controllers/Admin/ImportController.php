<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers;
use Illuminate\Http\Request;

use \App\Models\Event;
use \App\Models\User;
use \App\Models\Entity;
use Auth;
use ICal\ICal;
use DateTimeZone;
use DateTime;

/**
 * Handles administrator actions concerning elections.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-10-14
 */
class ImportAdminController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getIndex() {
		return view('admin.import.index')
		->with('entities', Entity::all());
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function postIndex(Request $request) {
		$this->validate($request, [
			'url' => 'required|url',
			'entity' => 'required'
		]);

		$entity = Entity::find($request->input('entity'));
		if ($entity === null) {
			return redirect('/admin/import')
			->with('error', 'Kunde inte hitta entiteten.');
		}

		$ical = new ICal;
		$ical->initURL($request->input('url'));

		$utc = new DateTimeZone('UTC');
		$normal = new DateTimeZone('Europe/Stockholm');
		$parseDate = function ($str) use ($utc, $normal) {
			if (substr($str, -1) == 'Z')
				$date = new DateTime(substr($str, 0, 15), $utc);
			else
				$date = new DateTime($str, $normal);
			return $date->setTimezone($normal)->format('Y-m-d H:i:s');
		};

		// https://calendar.google.com/calendar/ical/6a5rem0bbkrh5rber7a2sdpp48%40group.calendar.google.com/public/basic.ics
		$num = 0;
		foreach ($ical->events() as $e) {
			$event = new Event;
			$event->title = $e->summary == null ? 'Bokning' : $e->summary;
			$event->description = "";
			$event->start = $parseDate($e->dtstart);
			$event->end = $parseDate($e->dtend);

			$event->booked_by = 1;
			$event->entity_id = $entity->id;
			$event->approved = date("Y-m-d H:i:s");
			$event->approved_by = 1;
			$event->alcohol = 0;
			$event->created_by = 1;
			$event->save();
			$num ++;
		}

		return redirect('/admin/import')
		->with('success', $num . ' händelser importerades!');
	}

}
