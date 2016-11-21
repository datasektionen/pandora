<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;
use App\Helpers\EmailClient;

class Event extends Model
{
	protected $dates = ['created_at', 'updated_at', 'approved'];

	public static function boot() {
		parent::boot();

		// create a event to happen on saving
		static::saving(function($table)  {
			$table->created_by = Auth::user()->id;
		});
	}

	public function entity() {
		return $this->belongsTo('App\Models\Entity');
	}

	public function approve() {
		$this->approved = Carbon::now();
		$this->approved_by = Auth::user()->id;
		$this->save();

		EmailClient::sendBookingConfirmation($this);
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
		return $this->belongsTo('App\Models\User', 'created_by');
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
			->count() - 1;
	}
}
