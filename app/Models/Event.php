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
class Event extends Model {
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
	public function entity() {
		return $this->belongsTo('App\Models\Entity');
	}

	/**
	 * Defines relationship to person that initially booked this event.
	 * 
	 * @return relation
	 */
	public function author() {
		return $this->belongsTo('App\Models\User', 'booked_by');
	}

	/**
	 * Approves a booking. The authenticated user is set as approved_by.
	 * Replaces the event in replace_on_edit (ie removes it).
	 * Emails booking confirmation.
	 * 
	 * @return true
	 */
	public function approve() {
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
	public function edit() {
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
	public function decline() {
		$this->approved = null;
		$this->approved_by = null;
		$this->save();
		$this->delete();

		return true;
	}

	/**
	 * Returns number of events that this event collides with.
	 * 
	 * @return 
	 */
	public function collisions() {
		$start = $this->start;
		$end = $this->end;

		return Event::where(function ($query) use ($start, $end) {
			$query->where(function($query) use ($start) {
				$query->where('start', '>=', $start)
					->where('end', '<=', $start);
				})
				->orWhere(function($query) use ($end) {
					$query->where('start', '>=', $end)
						->where('end', '<=', $end);
				});
			})
			->where('id', '!=', $this->replaces_on_edit)
			->where('id', '!=', $this->id)
			->count();
	}
}
