<?php namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\User;
use App\Models\Entity;
use App\Http\Controllers;

use Auth;
use DateTimeZone;
use DateTime;
use ICal\ICal;

/**
 * Handles administrator actions concerning importing calendars into entities.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-23
 */
class ImportAdminController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Shows the form where admin can enter a URL to import.
	 * 
	 * @return view containing the form
	 */
	public function getIndex() {
		return view('admin.import.index')
			->with('entities', Entity::all());
	}

	/**
	 * Handles the post request on importing calendar.
	 * Uses the ICal library to parse the iCal, and saves event by event.
	 * 
	 * @param  Request $request the post request
	 * @return redirect to import page
	 */
	public function postIndex(Request $request) {
		$this->validate($request, [
			'url' => 'required|url',
			'entity' => 'required'
		]);

		$entity = Entity::findOrFail($request->input('entity'));

		// Parse the ical
		$ical = new ICal;
		$ical->initURL($request->input('url'));

		// Fix for timezones
		$utc = new DateTimeZone('UTC');
		$normal = new DateTimeZone('Europe/Stockholm');

		/**
		 * Parses a date from iCal into Y-m-d H:i:s local time.
		 * 
		 * @param string $str date on the format Ymd\THis(\Z?)
		 * @return the date as Y-m-d H:i:s
		 */
		$parseDate = function ($str) use ($utc, $normal) {
			if (substr($str, -1) == 'Z')
				$date = new DateTime(substr($str, 0, 15), $utc);
			else
				$date = new DateTime($str, $normal);
			return $date->setTimezone($normal)->format('Y-m-d H:i:s');
		};

		// Store number of imported events in $num for further presentation
		$num = 0;
		foreach ($ical->events() as $e) {
			if (Event::where('start', $parseDate($e->dtstart))->where('end', $parseDate($e->dtend))->count() > 0) {
				continue;
			}
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
			$event->save();
			$num++;
		}

		return redirect('/admin/import')
			->with('success', $num . ' händelser importerades!');
	}

}
