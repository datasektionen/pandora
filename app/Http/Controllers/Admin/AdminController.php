<?php namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Http\Controllers;
use App\Models\User;
use App\Models\Event;

/**
 * Main admin controller.
 *
 * @author Jonas Dahl <jonas@jdahl.se>
 * @version 2016-11-23
 */
class AdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Returns the main admin page.
     *
     * @return view
     */
    public function getIndex()
    {
        return view('admin.index');
    }
}
