<?php namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Http\Controllers;
use App\Models\Event;
use App\Models\User;
use App\Helpers\EmailClient;

use Auth;
use Session;

/**
 * Handles administrator actions concerning bookings.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-23
 */
class BookingAdminController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Shows all new bookings for this user as a list.
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
	 * Confirms a booking. Sends email to user.
	 *
	 * @param int $id the if of the event to accept
	 * @return view containing a list over elections
	 */
	public function getAccept($id) {
		$event = Event::findOrFail($id);

		// Approve and send mail
		$event->approve();
		EmailClient::sendBookingConfirmation($this);

		return redirect()
			->back()
			->with('success', 'Accepterade bokningen.');
	}

	/**
	 * Declines a booking.
	 * 
	 * @return view containing a list over elections
	 */
	public function getDecline($id) {
		$event = Event::findOrFail($id);

		// Decline and send sorry email
		EmailClient::sendBookingDeclined($this);
		$event->decline();

		return redirect()
			->back()
			->with('success', 'Nekade bokningen.');
	}

	/**
	 * Handles the post request when a user has confirmed/declined bookings.
	 *
	 * @param Request $request the request
	 * @return redirect to admin booking page
	 */
	public function postShow(Request $request) {
		$this->validate($request, [
			'booking' => 'required|array'
		]);

		foreach ($request->input('booking') as $id => $action) {
			$event = Event::find(intval($id));
			if ($event === null || $event->approved !== null) {
				continue;
			}

			if ($action == 'approve') {
				$event->approve();
			}
			if ($action == 'decline') {
				$event->delete();
			}
		}

		return redirect('admin/bookings');
	}
}
