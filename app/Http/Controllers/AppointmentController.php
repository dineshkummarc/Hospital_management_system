<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Patient;

/**
 * AppointmentController
 *
 * Renders the appointment management page.
 * All AJAX operations are handled via AppointmentApiController.
 */
class AppointmentController extends Controller
{
    public function index()
    {
        $doctors     = Doctor::active()->orderBy('fullName')->get();
        $departments = Department::active()->orderBy('departmentName')->get();
        $patients    = Patient::active()->orderBy('firstName')->get();

        return view('appointments.index', compact('doctors', 'departments', 'patients'));
    }
}
