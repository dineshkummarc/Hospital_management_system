<?php

namespace App\Http\Controllers;

use App\Models\Department;

/**
 * DoctorController
 *
 * Renders the doctor management page.
 * All AJAX operations are handled via DoctorApiController.
 */
class DoctorController extends Controller
{
    public function index()
    {
        $departments = Department::active()->orderBy('departmentName')->get();

        return view('doctors.index', compact('departments'));
    }
}
