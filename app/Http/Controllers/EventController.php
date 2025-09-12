<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Helpers\EmailClient;
use App\Models\Event;
use App\Models\User;
use App\Models\Entity;
use App\Http\Controllers;

use Auth;
use DateTime;
use DB;
use Session;

/**
 * Handles administrator actions concerning events.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-22
 */
class EventController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Shows a detailed view of event.
     *
     * @param int $id the id of the event to view
     * @return view showing the event or 404 if no booking could be found
     */
    public function getShow($id)
    {
        $event = Event::findOrFail($id);

        return view('events.index')
            ->with('event', $event);
    }

    /**
     * Shows a confirm deletion view.
     *
     * @param int $id the id of the event to delete
     * @return view showing the confirmation request or 404 if no event could be found
     */
    public function getDelete($id)
    {
        $event = Event::findOrFail($id);

        return view('events.delete')
            ->with('event', $event);
    }

    /**
     * Deletes event with given id if post field delete is set.
     * (Typically from submit button.) Also mails user and admin.
     *
     * @param int $id the id of the event to delete
     * @param Request $request the post request
     * @return redirect to main page /
     */
    public function postDelete($id, Request $request)
    {
        $this->validate($request, [
            'delete' => 'required'
        ]);

        $event = Event::findOrFail($id);
        $event->reason = $request->input('reason');
        $event->save();
        EmailClient::sendBookingDeleted($event);

        if ($event->isRecurring()) {
            switch ($request->input('recurring', '')) {
                case 'all':
                    // Delete all events in series
                    $allEvents = $event->recurringEvents()->get();
                    foreach ($allEvents as $e) {
                        $e->delete();
                    }
                    break;
                case 'following':
                    // Delete following events in series
                    $followingEvents = $event->followingRecurringEvents()->get();
                    foreach ($followingEvents as $e) {
                        $e->delete();
                    }
                    break;
                case 'this':
                    // Delete only this
                    $event->delete();
                    break;
                default:
                    return redirect()->back()
                        ->with('error', 'Du måste välja om du vill ta bort alla event i serien, efterföljande eller bara det aktuella.');
                    break;
            }
            return redirect('/')
                ->with('success', 'Bokningen togs bort.');
        }

        $event->delete();
        return redirect('/')
            ->with('success', 'Bokningen togs bort.');
    }

    /**
     * Shows an edit form for editing event.
     *
     * @param int $id the id of the event to edit
     * @return view with edit form or redirect to main page if booking not found
     */
    public function getEdit($id)
    {
        $event = Event::findOrFail($id);

        return view('events.edit')->with('event', $event);
    }

    /**
     * Handles the post request of editing an event. Creates a duplicate event
     * that is not approved, and keeps the old event. Sets the replaces_on_edit flag
     * on new model so it will be removed when the new booking is accepted.
     *
     * @param int $id id of the event to edit
     * @param Request $request the request
     * @return a redirect to event page
     */
    public function postEdit($id, Request $request)
    {
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

        // Copy old event into new event, and save it to be able to getDirty()
        $oldEvent = Event::findOrFail($id);
        if ($oldEvent->approved !== null) {
            $event = $oldEvent->replicate();
            $event->save();
            $event->replaces_on_edit = $oldEvent->id;
        } else {
            $event = clone $oldEvent;
        }

        // Set the new values for the event
        $event->start = date("Y-m-d H:i:s", strtotime($request->input('startdate') . ' ' . $request->input('starttime')));
        $event->end = date("Y-m-d H:i:s", strtotime($request->input('enddate') . ' ' . $request->input('endtime')));
        $event->title = $request->input('booker');
        $event->description = $request->input('reason');
        //$event->alcohol = (!$request->has('alcohol')) || ($request->has('alcohol') && $request->input('alcohol') === 'yes');
        // TODO: Above line fucks up since the boolean value of some reason is being cast to int
        // If fixed, also change in view

        // isDirty() checks if something has been changed. If not, delete the duplicate event
        if (!$event->isDirty()) {
            $event->delete();
            return redirect('/events/' . $event->id)
                ->with('success', 'Inga ändringar gjordes och därför sparades inget.');
        }

        // Otherwise, go on and email
        $dirty = $event->getDirty();
        $event->reason = $request->input('reason_edit');
        if (Auth::check() && Auth::user()->canManage($event->entity)) {
            // Approve directly if booking is made by admin
            // This will automatically remove the duplicate event
            $event->approve();
        } else {
            // Ask for confirmation if booking made by normal person
            $event->edit();
            EmailClient::sendBookingChanged($oldEvent, $event, $dirty);
            EmailClient::sendBookingChangedNotification($oldEvent, $event, $dirty);
        }

        return redirect('/events/' . $event->id)
            ->with('success', 'Bokningen ändrades och väntar nu på godkännande.');
    }
}
