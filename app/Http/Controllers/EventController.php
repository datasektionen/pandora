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
class EventController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function getShow($id) {
		$event = Event::find($id);
		if ($event == null) {
			return redirect()->back()->with('error', 'Bokningen kunde tyvärr inte hittas.');
		}

		return view('events.index')->with('event', $event);
	}

	public function getDelete($id) {
		$event = Event::find($id);
		if ($event == null) {
			return redirect()->back()->with('error', 'Bokningen kunde tyvärr inte hittas.');
		}

		return view('events.delete')->with('event', $event);
	}

	public function postDelete($id, Request $request) {
		$this->validate($request, [
			'delete' => 'required'
		]);

		$event = Event::find($id);
		EmailClient::sendBookingDeleted($event);
		$event->delete();

		return redirect('/')->with('success', 'Bokningen togs bort.');
	}

	public function getEdit($id) {
		$event = Event::find($id);
		if ($event == null) {
			return redirect()->back()->with('error', 'Bokningen kunde tyvärr inte hittas.');
		}

		return view('events.edit')->with('event', $event);
	}

	public function postEdit($id, Request $request) {
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

		$oldEvent = Event::find($id);
		if ($oldEvent == null) {
			return redirect()->back()->with('error', 'Bokningen kunde tyvärr inte hittas.');
		}

		$event = $oldEvent->replicate();
		$event->save();

		$event->start = date("Y-m-d H:i:s", strtotime($request->input('startdate') . ' ' . $request->input('starttime')));
		$event->end = date("Y-m-d H:i:s", strtotime($request->input('enddate') . ' ' . $request->input('endtime')));
		$event->title = $request->input('booker');
		$event->description = $request->input('reason');
		$event->replaces_on_edit = $oldEvent->id;
		//$event->alcohol = (!$request->has('alcohol')) || ($request->has('alcohol') && $request->input('alcohol') === 'yes');
		// TODO: Above line fucks up since the boolean value of some reason is being cast to int
		// If fixed, also change in view
		
		if (!$event->isDirty()) {
			$event->delete();
			return redirect('/events/' . $event->id)->with('success', '')
				->with('success', 'Inga ändringar gjordes och därför sparades inget.');
		}
		$dirty = $event->getDirty();

		if (Auth::check() && Auth::user()->isAdminFor($event->entity)) {
			$event->approve();
			EmailClient::sendBookingConfirmation($event);
		} else {
			$event->edit();
			EmailClient::sendBookingChanged($oldEvent, $event, $dirty);
			EmailClient::sendBookingChangedNotification($oldEvent, $event, $dirty);
		}

		return redirect('/events/' . $event->id)->with('success', 'Bokningen ändrades och väntar nu på godkännande.');
	}
}