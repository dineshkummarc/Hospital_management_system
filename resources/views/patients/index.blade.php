@extends('layouts.app')
@section('title', 'Patients Management')
@section('page-title', 'Patients')

@section('content')

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; align-items:center;">
        <span class="card-title">Patient Directory</span>
        <button class="btn btn-primary" onclick="openPatientModal()"><i class="fa fa-plus"></i> + Add Patient</button>
    </div>
    <div class="card-body" style="padding: 20px;">
        <!-- Filters & Search -->
        <div class="row mb-3" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <input type="text" id="patientSearchInput" class="form-control" placeholder="Search by name, phone, or email..." onkeyup="debounceFilterPatients()">
            </div>
            <div style="width:180px;">
                <select id="patientStatusFilter" class="form-control" onchange="filterPatients()">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="discharged">Discharged</option>
                    <option value="deceased">Deceased</option>
                </select>
            </div>
            <div style="width:200px;">
                <select id="patientDepartmentFilter" class="form-control" onchange="filterPatients()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->departmentID }}">{{ $dept->departmentName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Alert Notifications Container -->
        <div id="patientAlertContainer"></div>

        <!-- Loading Spinner -->
        <div id="patientLoadingSpinner" style="display:none; text-align:center; padding:30px;">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="sr-only">Loading patients...</span>
            </div>
            <p class="text-muted mt-2" style="font-size:13px;">Loading data...</p>
        </div>

        <!-- Patients Table -->
        <div class="table-wrap" id="patientTableContainer">
            <table class="table-premium" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>DOB / Age</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody">
                    <!-- Loaded dynamically via AJAX -->
                </tbody>
            </table>
            <div id="patientPagination" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;"></div>
        </div>
    </div>
</div>

<!-- Add/Edit Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" role="dialog" aria-hidden="true" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-dialog modal-lg" role="document" style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:650px; width:90%; padding:24px; color:white; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div class="modal-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
            <h5 class="modal-title" id="patientModalTitle" style="font-size:18px; font-weight:700;">Add New Patient</h5>
            <button type="button" class="close" onclick="closePatientModal()" style="background:none; border:none; color:white; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form id="patientForm" onsubmit="savePatient(event)">
            <input type="hidden" id="patientID" name="patientID" value="0">
            <div id="modalAlertContainer"></div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">First Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="firstName" name="firstName" class="form-control" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Last Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="lastName" name="lastName" class="form-control" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
            </div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Gender</label>
                    <select id="gender" name="gender" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Date of Birth</label>
                    <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
            </div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Email</label>
                    <input type="email" id="email" name="email" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
            </div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Department</label>
                    <select id="departmentID" name="departmentID" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->departmentID }}">{{ $dept->departmentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Status</label>
                    <select id="status" name="status" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                        <option value="active">Active</option>
                        <option value="discharged">Discharged</option>
                        <option value="deceased">Deceased</option>
                    </select>
                </div>
            </div>

            <div class="mb-3" style="margin-bottom:18px;">
                <label class="form-label" style="font-size:13px;">Address</label>
                <textarea id="address" name="address" class="form-control" rows="2" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;"></textarea>
            </div>

            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid rgba(255,255,255,0.1); padding-top:14px;">
                <button type="button" class="btn btn-secondary" onclick="closePatientModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSavePatient">Save Patient</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePatientModal" tabindex="-1" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:400px; width:90%; padding:20px; color:white; text-align:center;">
        <h5 style="font-size:18px; font-weight:700; margin-bottom:12px;">Confirm Delete</h5>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:20px;">Are you sure you want to delete patient <strong id="deletePatientName"></strong>? This action cannot be undone.</p>
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeDeletePatientModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="btnConfirmDeletePatient" onclick="confirmDeletePatient()">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('resources/js/patient.js') }}"></script>
@endpush
