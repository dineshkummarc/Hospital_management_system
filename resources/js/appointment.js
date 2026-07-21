/**
 * appointment.js
 * Clean AJAX logic for Appointment module.
 */

let appCurrentPage = 1;
let appDeleteTargetId = null;
let appDebounceTimer = null;

$(document).ready(function () {
    filterAppointments();
});

function debounceFilterAppointments() {
    clearTimeout(appDebounceTimer);
    appDebounceTimer = setTimeout(() => filterAppointments(1), 300);
}

function filterAppointments(page = 1) {
    appCurrentPage = page;
    const search = $('#appSearchInput').val();
    const status = $('#appStatusFilter').val();
    const doctorID = $('#appDoctorFilter').val();
    const departmentID = $('#appDepartmentFilter').val();
    const dateFrom = $('#appDateFromFilter').val();
    const dateTo = $('#appDateToFilter').val();

    $('#appLoadingSpinner').show();
    $('#appTableContainer').css('opacity', '0.5');

    $.getJSON('/api/appointments', {
        search: search,
        status: status,
        doctorID: doctorID,
        departmentID: departmentID,
        date_from: dateFrom,
        date_to: dateTo,
        page: page
    })
    .done(function (res) {
        if (res.status === 'success') {
            const data = Array.isArray(res.data) ? res.data : (res.data.data || []);
            renderAppointmentTable(data);
            if (res.data.current_page) {
                renderAppointmentPagination(res.data);
            } else {
                $('#appPagination').empty();
            }
        } else {
            showAppAlert('appAlertContainer', res.message || 'Failed to load appointments', 'danger');
        }
    })
    .fail(function () {
        showAppAlert('appAlertContainer', 'Error connecting to server', 'danger');
    })
    .always(function () {
        $('#appLoadingSpinner').hide();
        $('#appTableContainer').css('opacity', '1');
    });
}

function renderAppointmentTable(appointments) {
    const tbody = $('#appTableBody');
    tbody.empty();

    if (!appointments || appointments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No appointments found matching the search criteria.
                </td>
            </tr>
        `);
        return;
    }

    appointments.forEach(a => {
        const patientName = a.patientName || (a.patient ? (a.patient.firstName + ' ' + a.patient.lastName) : '—');
        const doctorName = a.doctorName || (a.doctor ? a.doctor.fullName : '—');
        const deptName = a.departmentName || (a.doctor && a.doctor.department ? a.doctor.department.departmentName : '—');

        let formattedTime = '—';
        if (a.appointmentTime) {
            const parts = a.appointmentTime.split(':');
            const hrs = parseInt(parts[0], 10);
            const mins = parts[1];
            const ampm = hrs >= 12 ? 'PM' : 'AM';
            const h12 = hrs % 12 || 12;
            formattedTime = `${h12}:${mins} ${ampm}`;
        }

        const dateStr = a.appointmentDate ? a.appointmentDate.split('T')[0] : '—';

        let badgeClass = 'secondary';
        if (a.status === 'pending') badgeClass = 'warning';
        else if (a.status === 'confirmed') badgeClass = 'info';
        else if (a.status === 'completed') badgeClass = 'success';
        else if (a.status === 'cancelled') badgeClass = 'danger';

        const tr = `
            <tr>
                <td>#${a.appointmentID}</td>
                <td><strong>${escapeAppHtml(patientName)}</strong></td>
                <td>${escapeAppHtml(doctorName)}</td>
                <td><span class="badge badge-secondary">${escapeAppHtml(deptName)}</span></td>
                <td>${dateStr} <span class="text-muted" style="font-size:12px;">${formattedTime}</span></td>
                <td style="color:var(--text-muted); font-size:12px;">${escapeAppHtml(a.reason || '—')}</td>
                <td><span class="badge badge-${badgeClass}">${a.status ? a.status.toUpperCase() : 'PENDING'}</span></td>
                <td style="text-align:right;">
                    <button class="btn btn-secondary btn-sm" onclick="editAppointment(${a.appointmentID})" title="Edit">✏️ Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="openDeleteAppModal(${a.appointmentID}, '#${a.appointmentID} for ${escapeAppHtml(patientName)}')">🗑️ Delete</button>
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function renderAppointmentPagination(meta) {
    const container = $('#appPagination');
    container.empty();

    if (!meta.total || meta.total <= meta.per_page) return;

    const current = meta.current_page;
    const last = meta.last_page;

    let html = `<span style="font-size:13px; color:var(--text-muted);">Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total} appointments</span>`;
    html += `<div style="display:flex; gap:6px;">`;

    if (current > 1) {
        html += `<button class="btn btn-secondary btn-sm" onclick="filterAppointments(${current - 1})">Prev</button>`;
    }
    if (current < last) {
        html += `<button class="btn btn-secondary btn-sm" onclick="filterAppointments(${current + 1})">Next</button>`;
    }
    html += `</div>`;

    container.html(html);
}

function openAppModal() {
    $('#appointmentID').val(0);
    $('#appForm')[0].reset();
    $('#appModalTitle').text('Schedule Appointment');
    $('#appModalAlertContainer').empty();
    $('#appModal').css('display', 'flex');
}

function closeAppModal() {
    $('#appModal').css('display', 'none');
}

function editAppointment(id) {
    $('#appModalAlertContainer').empty();
    $.getJSON('/api/appointments/' + id)
    .done(function (res) {
        if (res.status === 'success' && res.data) {
            const a = res.data;
            $('#appointmentID').val(a.appointmentID);
            $('#appPatientID').val(a.patientID);
            $('#appDoctorID').val(a.doctorID);
            $('#appointmentDate').val(a.appointmentDate ? a.appointmentDate.split('T')[0] : '');
            $('#appointmentTime').val(a.appointmentTime ? a.appointmentTime.substring(0, 5) : '');
            $('#reason').val(a.reason || '');
            $('#appStatus').val(a.status || 'pending');

            $('#appModalTitle').text('Edit Appointment #' + a.appointmentID);
            $('#appModal').css('display', 'flex');
        } else {
            showAppAlert('appAlertContainer', res.message || 'Error fetching appointment details', 'danger');
        }
    })
    .fail(function () {
        showAppAlert('appAlertContainer', 'Failed to fetch appointment details', 'danger');
    });
}

function saveAppointment(e) {
    e.preventDefault();
    const btn = $('#btnSaveApp');
    const originalText = btn.text();
    btn.prop('disabled', true).text('Saving...');

    const appointmentID = $('#appointmentID').val();
    const data = {
        appointmentID: appointmentID,
        patientID: $('#appPatientID').val(),
        doctorID: $('#appDoctorID').val(),
        appointmentDate: $('#appointmentDate').val(),
        appointmentTime: $('#appointmentTime').val(),
        reason: $('#reason').val(),
        status: $('#appStatus').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    const url = appointmentID > 0 ? '/api/appointments/' + appointmentID : '/api/appointments';
    const method = appointmentID > 0 ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json'
    })
    .done(function (res) {
        if (res.status === 'success') {
            closeAppModal();
            showAppAlert('appAlertContainer', res.message, 'success');
            filterAppointments(appCurrentPage);
        } else {
            showAppAlert('appModalAlertContainer', res.message || 'Error saving appointment', 'danger');
        }
    })
    .fail(function (xhr) {
        let msg = 'Validation error occurred';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
        }
        showAppAlert('appModalAlertContainer', msg, 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text(originalText);
    });
}

function openDeleteAppModal(id, info) {
    appDeleteTargetId = id;
    $('#deleteAppInfo').text(info);
    $('#deleteAppModal').css('display', 'flex');
}

function closeDeleteAppModal() {
    appDeleteTargetId = null;
    $('#deleteAppModal').css('display', 'none');
}

function confirmDeleteAppointment() {
    if (!appDeleteTargetId) return;
    const btn = $('#btnConfirmDeleteApp');
    btn.prop('disabled', true).text('Deleting...');

    $.ajax({
        url: '/api/appointments/' + appDeleteTargetId,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json'
    })
    .done(function (res) {
        closeDeleteAppModal();
        if (res.status === 'success') {
            showAppAlert('appAlertContainer', res.message, 'success');
            filterAppointments(appCurrentPage);
        } else {
            showAppAlert('appAlertContainer', res.message || 'Error deleting appointment', 'danger');
        }
    })
    .fail(function () {
        closeDeleteAppModal();
        showAppAlert('appAlertContainer', 'Failed to delete appointment', 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text('Delete');
    });
}

function showAppAlert(containerId, message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const html = `
        <div class="alert ${alertClass} animate-in" style="margin-bottom:15px; padding:12px; border-radius:6px;">
            ${escapeAppHtml(message)}
        </div>
    `;
    $('#' + containerId).html(html);
    setTimeout(() => {
        $('#' + containerId + ' .alert').fadeOut(500, function () { $(this).remove(); });
    }, 4000);
}

function escapeAppHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
