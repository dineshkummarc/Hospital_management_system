<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DepartmentApiController
 *
 * REST API endpoints for department management.
 */
class DepartmentApiController extends Controller
{
    /**
     * GET /api/departments
     * Returns a filtered list of departments with doctor/patient counts.
     */
    public function filterDepartments(Request $request): JsonResponse
    {
        if ($this->isMysql() && ! $request->filled('simple')) {
            $rows = DB::select('CALL SP_filter_departments(?, ?)', [
                $request->input('search'),
                $request->input('status'),
            ]);
            return response()->json(['status' => 'success', 'data' => $rows]);
        }

        $query = Department::withCount(['doctors', 'patients']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('departmentName', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $departments = $query->orderBy('departmentName')->get();

        return response()->json(['status' => 'success', 'data' => $departments]);
    }

    /**
     * GET /api/departments/{id}
     * Returns detailed stats for a single department.
     */
    public function getDepartmentDetails(int $id): JsonResponse
    {
        if ($this->isMysql()) {
            $rows = DB::select('CALL SP_get_department_details(?)', [$id]);
            if (empty($rows)) {
                return response()->json(['status' => 'error', 'message' => 'Department not found.'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $rows[0]]);
        }

        $dept = Department::withCount(['doctors', 'patients'])->find($id);
        if (! $dept) {
            return response()->json(['status' => 'error', 'message' => 'Department not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $dept]);
    }

    /**
     * POST /api/departments
     * Saves (inserts or updates) a department.
     */
    public function saveDepartment(Request $request): JsonResponse
    {
        $departmentID = (int) $request->input('departmentID', 0);

        $data = $request->validate([
            'departmentName' => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'status'         => 'nullable|in:active,inactive',
        ]);

        if ($this->isMysql()) {
            // Check for duplicate name
            $check = DB::select('CALL SP_check_department(?, ?, @exists)', [
                $data['departmentName'],
                $departmentID ?: null,
            ]);
            $exists = DB::select('SELECT @exists AS exists')[0]->exists ?? 0;
            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'A department with this name already exists.',
                ], 422);
            }

            DB::statement('CALL SP_save_department(?, ?, ?, ?, @result_id)', [
                $departmentID,
                $data['departmentName'],
                $data['description'] ?? null,
                $data['status']      ?? 'active',
            ]);
            $resultId = DB::select('SELECT @result_id AS result_id')[0]->result_id ?? null;
            $dept     = Department::find($resultId);
        } else {
            if ($departmentID > 0) {
                $dept = Department::findOrFail($departmentID);
                $dept->update($data);
            } else {
                $dept = Department::create(array_merge($data, ['status' => $data['status'] ?? 'active']));
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => $departmentID > 0 ? 'Department updated successfully.' : 'Department saved successfully.',
            'data'    => $dept,
        ], $departmentID > 0 ? 200 : 201);
    }

    /**
     * DELETE /api/departments/{id}
     */
    public function deleteDepartment(int $id): JsonResponse
    {
        $dept = Department::find($id);
        if (! $dept) {
            return response()->json(['status' => 'error', 'message' => 'Department not found.'], 404);
        }

        if ($this->isMysql()) {
            DB::statement('CALL SP_delete_department(?, @affected)', [$id]);
        } else {
            $dept->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Department deleted successfully.',
        ]);
    }

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
