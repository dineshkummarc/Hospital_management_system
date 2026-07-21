@extends('layouts.app')
@section('title', 'Appointments Management')
@section('page-title', 'Appointments')

@section('content')

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; align-items:center;">
        <span class="card-title">Appointments Directory</span>
        <button class="btn btn-primary" onclick="openAppModal()"><i class="fa fa-plus"></i> + Schedule Appointment</button>
    </div>
    <div class="card-body" style="padding: 20px;">
        <!-- Filters & Search -->
        <div class="row mb-3" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:180px;">
                <input type="text" id="appSearchInput" class="form-control" placeholder="Search patient or doctor name..." onkeyup="debounceFilterAppointments()">
            </div>
            <div style="width:140px;">
                <select id="appStatusFilter" class="form-control" onchange="filterAppointments()">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div style="width:160px;">
                <select id="appDoctorFilter" class="form-control" onchange="filterAppointments()">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                    <option value="{{ $doc->doctorID }}">{{ $doc->fullName }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width:160px;">
                <select id="appDepartmentFilter" class="form-control" onchange="filterAppointments()">
                    <option value="">All Depts</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->departmentID }}">{{ $dept->departmentName }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width:140px;">
                <input type="date" id="appDateFromFilter" class="form-control" onchange="filterAppointments()" title="From Date">
            </div>
            <div style="width:140px;">
                <input type="date" id="appDateToFilter" class="form-control" onchange="filterAppointments()" title="To Date">
            </div>
        </div>

        <div id="appAlertContainer"></div>

        <!-- Loading Spinner -->
        <div id="appLoadingSpinner" style="display:none; text-align:center; padding:30px;">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <p class="text-muted mt-2" style="font-size:13px;">Loading appointments...</p>
        </div>

        <!-- Appointments Table -->
        <div class="table-wrap" id="appTableContainer">
            <table class="table-premium" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Date & Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="appTableBody">
                    <!-- Loaded dynamically via AJAX -->
                </tbody>
            </table>
            <div id="appPagination" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;"></div>
        </div>
    </div>
</div>

<!-- Add/Edit Appointment Modal -->
<div class="modal fade" id="appModal" tabindex="-1" role="dialog" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div class="modal-dialog modal-lg" style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:650px; width:90%; padding:24px; color:white; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div class="modal-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
            <h5 class="modal-title" id="appModalTitle" style="font-size:18px; font-weight:700;">Schedule Appointment</h5>
            <button type="button" class="close" onclick="closeAppModal()" style="background:none; border:none; color:white; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form id="appForm" onsubmit="saveAppointment(event)">
            <input type="hidden" id="appointmentID" name="appointmentID" value="0">
            <div id="appModalAlertContainer"></div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Patient <span style="color:#ef4444;">*</span></label>
                    <select id="appPatientID" name="patientID" class="form-control" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $pt)
                        <option value="{{ $pt->patientID }}">{{ $pt->full_name }} (Phone: {{ $pt->phone ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Doctor <span style="color:#ef4444;">*</span></label>
                    <select id="appDoctorID" name="doctorID" class="form-control" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                        <option value="">-- Select Doctor --</option>
                        @foreach($doctors as $doc)
                        <option value="{{ $doc->doctorID }}">{{ $doc->fullName }} ({{ $doc->specialization ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row" style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" id="appointmentDate" name="appointmentDate" class="form-control" required style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:13px;">Time</label>
                    <input type="time" id="appointmentTime" name="appointmentTime" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                </div>
            </div>

            <div class="mb-3" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:13px;">Reason for Appointment</label>
                <textarea id="reason" name="reason" class="form-control" rows="2" placeholder="Primary symptoms or consultation reason..." style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;"></textarea>
            </div>

            <div class="mb-3" style="margin-bottom:18px;">
                <label class="form-label" style="font-size:13px;">Status</label>
                <select id="appStatus" name="status" class="form-control" style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:white; border-radius:6px;">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid rgba(255,255,255,0.1); padding-top:14px;">
                <button type="button" class="btn btn-secondary" onclick="closeAppModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveApp">Save Appointment</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAppModal" tabindex="-1" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div style="background:var(--navy-800, #0d1b2a); border:1px solid var(--glass-border); border-radius:12px; max-width:400px; width:90%; padding:20px; color:white; text-align:center;">
        <h5 style="font-size:18px; font-weight:700; margin-bottom:12px;">Confirm Delete</h5>
        <p style="font-size:14px; color:var(--text-muted); margin-bottom:20px;">Are you sure you want to delete appointment <strong id="deleteAppInfo"></strong>?</p>
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteAppModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="btnConfirmDeleteApp" onclick="confirmDeleteAppointment()">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('resources/js/appointment.js') }}"></script>
@endpush
