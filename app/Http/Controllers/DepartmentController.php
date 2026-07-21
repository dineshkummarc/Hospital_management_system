<?php

namespace App\Http\Controllers;

/**
 * DepartmentController
 *
 * Renders the department management page.
 * All AJAX operations are handled via DepartmentApiController.
 */
class DepartmentController extends Controller
{
    public function index()
    {
        return view('departments.index');
    }
}
