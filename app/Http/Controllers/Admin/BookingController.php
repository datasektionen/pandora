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
use Auth;
use Session;

/**
 * Handles administrator actions concerning elections.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-10-14
 */
class BookingAdminController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getShow() {
		$events = Auth::user()
			->decisionEvents()
			->paginate(20);

		return view('admin.bookings.index')
			->with('bookings', $events);
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getAccept($id) {
		$event = Event::find($id);
		$entity = $event->entity;
		if (!Auth::check() || !Auth::user()->isAdminFor($entity)) {
			return redirect()->back()->with('error', 'Du får inte göra det här.');
		}
		if ($event === null) {
			return redirect()->back()->with('error', 'Kunde inte hitta bokningen.');
		}
		$event->approve();

		return redirect()->back()->with('success', 'Accepterade bokningen.');
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getDecline($id) {
		$event = Event::find($id);
		$entity = $event->entity;
		if (!Auth::check() || !Auth::user()->isAdminFor($entity)) {
			return redirect()->back()->with('error', 'Du får inte göra det här.');
		}
		if ($event === null) {
			return redirect()->back()->with('error', 'Kunde inte hitta bokningen.');
		}
		$event->decline();

		return redirect()->back()->with('success', 'Nekade bokningen.');
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function postShow(Request $request) {
		$this->validate($request, [
			'booking' => 'required|array'
		]);

		foreach ($request->input('booking') as $id => $ans) {
			$event = Event::find(intval($id));
			if ($event === null || $event->approved !== null) {
				continue;
			}
			echo "event";
			if ($ans == 'approve') {
				$event->approve();
			}
			if ($ans == 'decline') {
				$event->delete();
			}
		}

		return redirect('admin/bookings');
	}
}
