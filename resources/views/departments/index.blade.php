@extends('layouts.app')
@section('title', 'Departments Management')
@section('page-title', 'Departments')

@section('content')

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; align-items:center;">
        <span class="card-title">Hospital Departments</span>
        <button class="btn btn-primary" onclick="openDeptModal()"><i class="fa fa-plus"></i> + Add Department</button>
    </div>
    <div class="card-body" style="padding: 20px;">
        <!-- Search & Filter -->
        <div class="row mb-3" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <input type="text" id="deptSearchInput" class="form-control" placeholder="Search by department name or description..." onkeyup="debounceFilterDepartments()">
            </div>
            <div style="width:180px;">
                <select id="deptStatusFilter" class="form-control" onchange="filterDepartments()">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div id="deptAlertContainer"></div>

        <!-- Loading Spinner -->
        <div id="deptLoadingSpinner" style="display:none; text-align:center; padding:30px;">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <p class="text-muted mt-2" style="font-size:13px;">Loading departments...</p>
        </div>

        <!-- Departments Grid/Table -->
        <div class="table-wrap" id="deptTableContainer">
            <table class="table-premium" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Doctors Count</th>
                        <th>Patients Count</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="deptTableBody">
                    <!-- Loaded dynamically via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div class="modal fade" id="deptModal" tabindex="-1" role="dialog" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-dialog" style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:550px; width:90%; padding:24px; color:white; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div class="modal-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
            <h5 class="modal-title" id="deptModalTitle" style="font-size:18px; font-weight:700;">Add New Department</h5>
            <button type="button" class="close" onclick="closeDeptModal()" style="background:none; border:none; color:white; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form id="deptForm" onsubmit="saveDepartment(event)">
            <input type="hidden" id="departmentID" name="departmentID" value="0">
            <div id="deptModalAlertContainer"></div>

            <div class="mb-3" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:13px;">Department Name <span style="color:#ef4444;">*</span></label>
                <input type="text" id="departmentName" name="departmentName" class="form-control" placeholder="e.g. Cardiology, Radiology" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
            </div>

            <div class="mb-3" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:13px;">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Overview of department functions..." style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;"></textarea>
            </div>

            <div class="mb-3" style="margin-bottom:18px;">
                <label class="form-label" style="font-size:13px;">Status</label>
                <select id="deptStatus" name="status" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid rgba(255,255,255,0.1); padding-top:14px;">
                <button type="button" class="btn btn-secondary" onclick="closeDeptModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveDept">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteDeptModal" tabindex="-1" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:400px; width:90%; padding:20px; color:white; text-align:center;">
        <h5 style="font-size:18px; font-weight:700; margin-bottom:12px;">Confirm Delete</h5>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:20px;">Are you sure you want to delete department <strong id="deleteDeptName"></strong>?</p>
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteDeptModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="btnConfirmDeleteDept" onclick="confirmDeleteDepartment()">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('resources/js/department.js') }}"></script>
@endpush
