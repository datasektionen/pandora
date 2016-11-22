<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;
use App\Helpers\EmailClient;

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
	 * Called when this model is approved.
	 * 
	 * @return true
	 */
	public function approve() {
		$this->approved = Carbon::now();
		$this->approved_by = Auth::user()->id;
		$this->save();

		if ($this->replaces_on_edit !== null) {
			$e = Event::find($this->replaces_on_edit);
			if ($e !== null) {
				$e->delete();
			}
		}

		EmailClient::sendBookingConfirmation($this);
		return true;
	}

	/**
	 * Called when this model is edited. Approved by will disappear.
	 * 
	 * @return true
	 */
	public function edit() {
		$this->approved = null;
		$this->approved_by = null;
		$this->save();
		return true;
	}

	public function decline() {
		$this->approved = null;
		$this->approved_by = null;
		$this->save();
		EmailClient::sendBookingDeclined($this);
		$this->delete();

		return true;
	}

	public function author() {
		return $this->belongsTo('App\Models\User', 'booked_by');
	}

	public function collisions() {
		$start = $this->start;
		$end = $this->end;

		return Event::where(function($query) use ($start) {
				$query->where('start', '>=', $start)
					->where('end', '<=', $start);
			})
			->orWhere(function($query) use ($end) {
				$query->where('end', '>=', $end)
					->where('start', '<=', $end);
			})
			->where('id', '!=', $this->replaces_on_edit)
			->count() - 1;
	}
}
