<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Helpers\EmailClient;

use Carbon\Carbon;
use Auth;

/**
 * An event describes a booking. Contains starting, ending dates,
 * title and some more.
 *
 * @author Jonas Dahl <jonadahl@kth.se>
 * @version 2016-11-22
 */
class Event extends Model
{
    /**
     * Defines all columns in the database that laravel should treat as dates.
     * These dates will be Carbon dates when handled.
     *
     * @var array of strings
     */
    protected $dates = ['start', 'end', 'created_at', 'updated_at', 'approved'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['alcohol' => 'boolean'];

    /**
     * Defines relation the the entity it belongs to.
     *
     * @return relation
     */
    public function entity()
    {
        return $this->belongsTo('App\Models\Entity');
    }

    /**
     * Defines relationship to person that initially booked this event.
     *
     * @return relation
     */
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'booked_by');
    }

    /**
     * Approves a booking. The authenticated user is set as approved_by.
     * Replaces the event in replace_on_edit (ie removes it).
     * Emails booking confirmation.
     *
     * @return true
     */
    public function approve()
    {
        $this->approved = Carbon::now();
        $this->approved_by = Auth::user()->id;

        if ($this->replaces_on_edit !== null) {
            $e = Event::find($this->replaces_on_edit);
            if ($e !== null) {
                $e->delete();
            }
            $this->replaces_on_edit = null;
        }
        $this->save();

        return true;
    }

    /**
     * Removes approve of a booking.
     *
     * @return true
     */
    public function edit()
    {
        $this->approved = null;
        $this->approved_by = null;
        $this->save();
        return true;
    }

    /**
     * Declines a booking. Deletes the event and emails user.
     *
     * @return true
     */
    public function decline()
    {
        $this->approved = null;
        $this->approved_by = null;
        $this->save();
        $this->delete();

        return true;
    }

    /**
     * Returns number of events that this event collides with.
     * Also takes parent (part_of) entity events into account.
     *
     * @return int number of such collisions
     */
    public function collisions()
    {
        $start = $this->start;
        $end = $this->end;

        return $q = Event::where('start', '<', $end)
            ->where('end', '>', $start)
            ->where(function ($query) {
                $query->where('entity_id', $this->entity->part_of)
                    ->orWhere('entity_id', $this->entity->id);
            })
            ->where('events.id', '!=', $this->replaces_on_edit)
            ->where('events.id', '!=', $this->id)
            ->count();
    }

    /**
     * Returns number of events that this event collides with.
     * Also takes parent (part_of) and children entity events into account.
     *
     * @return int number of such collisions
     */
    public function weakCollisions()
    {
        $start = $this->start;
        $end = $this->end;

        return $q = Event::where('start', '<', $end)
            ->join('entities', 'entities.id', 'events.entity_id')
            ->where('end', '>', $start)
            ->where(function ($query) {
                $query->where('entity_id', $this->entity->part_of)
                    ->orWhere('entity_id', $this->entity->id)
                    ->orWhere('part_of', $this->entity->id);
            })
            ->where('events.id', '!=', $this->replaces_on_edit)
            ->where('events.id', '!=', $this->id)
            ->count();
    }

    /**
     * Returns a relation to the recurring_of event.
     *
     * @return Relation
     */
    public function recurringOrigin()
    {
        return $this->belongsTo('App\Models\Event', 'recurring_of');
    }

    /**
     * Returns true if this event is recurring, false otherwise.
     *
     * @return boolean
     */
    public function isRecurring()
    {
        return $this->recurring_of === null || Event::where('recurring_of', '=', $this->id)->count() === 0;
    }

    /**
     * Returns all the recurring events for this event.
     *
     * @return [Events]
     */
    public function recurringEvents()
    {
        if ($this->recurring_of === null) {
            return Event::where('recurring_of', $this->id)->union(Event::where('id', $this->id));
        }
        return Event::where('recurring_of', '=', $this->recurring_of)->union(Event::where('id', $this->recurring_of));
    }

    /**
     * Returns all the following recurring events.
     *
     * @return [Events]
     */
    public function followingRecurringEvents()
    {
        if ($this->recurring_of === null) {
            return Event::where('recurring_of', $this->id)->where('start', '>', $this->start)->union(Event::where('id', $this->id));
        }
        return Event::where('recurring_of', '=', $this->recurring_of)->where('start', '>', $this->start)->union(Event::where('id', $this->id));
    }
}
