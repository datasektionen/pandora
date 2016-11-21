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
use \App\Models\Entity;
use Auth;

/**
 * Handles administrator actions concerning elections.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-10-14
 */
class EntityAdminController extends BaseController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getShow() {
		$entities = Entity::forAuthUser()->orderBy('name')->paginate(20);

		return view('admin.entities.index')
			->with('entities', $entities);
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getNew() {
		return view('admin.entities.new');
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function postNew(Request $request) {
		$this->validate($request, [
			'name' => 'required',
			'description' => 'required',
			'pls_group' => 'required'
		]);

		$entity = new Entity;
		$entity->name = $request->input('name');
		$entity->description = $request->input('description');
		$entity->pls_group = $request->input('pls_group');
		$entity->save();

		return redirect('/admin/entities')->with('success', $request->input('name') . ' skapades!');
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function getEdit($id) {
		$entity = Entity::find($id);
		if ($entity === null) {
			return redirect()->back()->with('error', 'Hittade inte entiteten att ändra.');
		}
		return view('admin.entities.edit')
			->with('entity', $entity);
	}

	/**
	 * Shows all elections as a list.
	 * 
	 * @return view containing a list over elections
	 */
	public function postEdit($id, Request $request) {
		$this->validate($request, [
			'name' => 'required',
			'description' => 'required',
			'pls_group' => 'required'
		]);

		$entity = Entity::find($id);
		if ($entity === null) {
			return redirect()->back()->with('error', 'Hittade inte entiteten att ändra.');
		}
		$entity->name = $request->input('name');
		$entity->description = $request->input('description');
		$entity->save();

		return redirect('/admin/entities')->with('success', $request->input('name') . ' ändrades!');
	}

}
