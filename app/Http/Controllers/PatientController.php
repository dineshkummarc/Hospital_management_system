<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * PatientController
 *
 * Handles web routes for patient management.
 * All AJAX operations (save, filter, delete) are handled via PatientApiController.
 */
class PatientController extends Controller
{
    /**
     * Display the patients management page.
     * The table is populated via AJAX (filterPatients endpoint).
     */
    public function index()
    {
        $departments = Department::active()->orderBy('departmentName')->get();

        return view('patients.index', compact('departments'));
    }
}
