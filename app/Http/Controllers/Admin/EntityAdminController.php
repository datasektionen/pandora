<?php namespace App\Http\Controllers\Admin;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\User;
use App\Models\Entity;
use App\Http\Controllers;

use Auth;

/**
 * Handles administrator actions concerning entities.
 *
 * @author  Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-23
 */
class EntityAdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Shows all entities for the authenticated user as a list.
     *
     * @return view containing a list over entities
     */
    public function getShow()
    {
        $entities = Entity::forAuthUser()->orderBy('name')->paginate(20);

        return view('admin.entities.index')
            ->with('entities', $entities);
    }

    /**
     * Create new entity action. Shows a form.
     *
     * @return view with form
     */
    public function getNew()
    {
        return view('admin.entities.new');
    }

    /**
     * Handles the post request when creating a new entity.
     *
     * @param Request $request the post request
     * @return redirect to admin entities page
     */
    public function postNew(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'pls_group' => 'required'
        ]);

        // Create entity
        $entity = new Entity;
        $entity->name = $request->input('name');
        $entity->description = $request->input('description');
        $entity->notify_email = $request->input('notify_email');
        $entity->pls_group = $request->input('pls_group');
        $entity->alcohol_question = $request->input('alcohol_question') == 'yes';
        $entity->show_pending_bookings = $request->input('show_pending_bookings') == 'yes';
        $entity->part_of = $request->input('part_of');
        $entity->fa_icon = $request->input('fa_icon');
        $entity->contract_url = $request->input('contract') === 'yes' ? $request->input('contract_url') : null;
        $entity->rank = $request->input('rank');
        $entity->save();

        return redirect('/admin/entities')
            ->with('success', $request->input('name') . ' skapades!');
    }

    /**
     * Edit entity action. Shows a form.
     *
     * @return view with form
     */
    public function getEdit($id)
    {
        $entity = Entity::findOrFail($id);

        return view('admin.entities.edit')
            ->with('entity', $entity);
    }

    /**
     * Handles the post request when editing an entity.
     *
     * @param Request $request the post request
     * @return redirect to admin entities page
     */
    public function postEdit($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required'
        ]);

        // Do the editing
        $entity = Entity::findOrFail($id);
        $entity->name = $request->input('name');
        $entity->description = $request->input('description');
        $entity->ruta_med_stuff = $request->input('ruta_med_stuff');
        $entity->notify_email = $request->input('notify_email');
        $entity->alcohol_question = $request->input('alcohol_question') == 'yes';
        $entity->show_pending_bookings = $request->input('show_pending_bookings') == 'yes';
        $entity->part_of = $request->input('part_of');
        $entity->fa_icon = $request->input('fa_icon');
        $entity->contract_url = $request->input('contract') === 'yes' ? $request->input('contract_url') : null;
        $entity->rank = $request->input('rank');
        $entity->save();

        return redirect('/admin/entities')
            ->with('success', $request->input('name') . ' ändrades!');
    }

}
