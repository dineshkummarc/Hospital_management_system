@extends('layouts.app')
@section('title', 'Doctors Management')
@section('page-title', 'Doctors')

@section('content')

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; align-items:center;">
        <span class="card-title">Doctor Directory</span>
        <button class="btn btn-primary" onclick="openDoctorModal()"><i class="fa fa-plus"></i> + Add Doctor</button>
    </div>
    <div class="card-body" style="padding: 20px;">
        <!-- Filters & Search -->
        <div class="row mb-3" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <input type="text" id="doctorSearchInput" class="form-control" placeholder="Search by name, email, specialization..." onkeyup="debounceFilterDoctors()">
            </div>
            <div style="width:180px;">
                <select id="doctorStatusFilter" class="form-control" onchange="filterDoctors()">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="on_leave">On Leave</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div style="width:200px;">
                <select id="doctorDepartmentFilter" class="form-control" onchange="filterDoctors()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->departmentID }}">{{ $dept->departmentName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="doctorAlertContainer"></div>

        <!-- Loading Spinner -->
        <div id="doctorLoadingSpinner" style="display:none; text-align:center; padding:30px;">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <p class="text-muted mt-2" style="font-size:13px;">Loading doctors...</p>
        </div>

        <!-- Doctors Table -->
        <div class="table-wrap" id="doctorTableContainer">
            <table class="table-premium" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Specialization</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="doctorTableBody">
                    <!-- Loaded dynamically via AJAX -->
                </tbody>
            </table>
            <div id="doctorPagination" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;"></div>
        </div>
    </div>
</div>

<!-- Add/Edit Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" role="dialog" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-dialog modal-lg" style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:650px; width:90%; padding:24px; color:white; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div class="modal-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
            <h5 class="modal-title" id="doctorModalTitle" style="font-size:18px; font-weight:700;">Add New Doctor</h5>
            <button type="button" class="close" onclick="closeDoctorModal()" style="background:none; border:none; color:white; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form id="doctorForm" onsubmit="saveDoctor(event)">
            <input type="hidden" id="doctorID" name="doctorID" value="0">
            <div id="doctorModalAlertContainer"></div>

            <div class="mb-3" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:13px;">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" id="fullName" name="fullName" class="form-control" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
            </div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Specialization</label>
                    <input type="text" id="specialization" name="specialization" class="form-control" placeholder="e.g. Cardiology, Neurology" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Department</label>
                    <select id="docDepartmentID" name="departmentID" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->departmentID }}">{{ $dept->departmentName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Email</label>
                    <input type="email" id="docEmail" name="email" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Phone</label>
                    <input type="text" id="docPhone" name="phone" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
            </div>

            <div class="mb-3" style="margin-bottom:18px;">
                <label class="form-label" style="font-size:13px;">Status</label>
                <select id="docStatus" name="status" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                    <option value="active">Active</option>
                    <option value="on_leave">On Leave</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid rgba(255,255,255,0.1); padding-top:14px;">
                <button type="button" class="btn btn-secondary" onclick="closeDoctorModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveDoctor">Save Doctor</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Doctor Confirmation Modal -->
<div class="modal fade" id="deleteDoctorModal" tabindex="-1" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:400px; width:90%; padding:20px; color:white; text-align:center;">
        <h5 style="font-size:18px; font-weight:700; margin-bottom:12px;">Confirm Delete</h5>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:20px;">Are you sure you want to delete doctor <strong id="deleteDoctorName"></strong>?</p>
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteDoctorModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="btnConfirmDeleteDoctor" onclick="confirmDeleteDoctor()">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('resources/js/doctor.js') }}"></script>
@endpush
