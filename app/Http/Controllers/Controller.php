<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\Entity;

/**
 * Main controller where actions that doesn't fit anywhere else are placed.
 *
 * @author Jonas Dahl <jonadahl@kth.se>
 * @version 2016-11-23
 */
class Controller extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Returns the main page.
	 * 
	 * @return response welcome view
	 */
	public function getIndex() {
		return view('welcome')
			->with('entities', Entity::all());
	}
}
