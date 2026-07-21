/**
 * patient.js
 * Clean, simplified jQuery AJAX logic for Patient module.
 * - $.getJSON()
 * - $.post() / $.ajax()
 * - Reusable helper functions
 * - Avoids deeply nested callbacks
 */

let currentPage = 1;
let deleteTargetId = null;
let debounceTimer = null;

$(document).ready(function () {
    filterPatients();
});

function debounceFilterPatients() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => filterPatients(1), 300);
}

function filterPatients(page = 1) {
    currentPage = page;
    const search = $('#patientSearchInput').val();
    const status = $('#patientStatusFilter').val();
    const departmentID = $('#patientDepartmentFilter').val();

    $('#patientLoadingSpinner').show();
    $('#patientTableContainer').css('opacity', '0.5');

    $.getJSON('/api/patients', {
        search: search,
        status: status,
        departmentID: departmentID,
        page: page
    })
    .done(function (res) {
        if (res.status === 'success') {
            renderPatientTable(res.data.data);
            renderPatientPagination(res.data);
        } else {
            showAlert('patientAlertContainer', res.message || 'Failed to load patients', 'danger');
        }
    })
    .fail(function (xhr) {
        showAlert('patientAlertContainer', 'Error connecting to server', 'danger');
    })
    .always(function () {
        $('#patientLoadingSpinner').hide();
        $('#patientTableContainer').css('opacity', '1');
    });
}

function renderPatientTable(patients) {
    const tbody = $('#patientTableBody');
    tbody.empty();

    if (!patients || patients.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No patients found matching the search criteria.
                </td>
            </tr>
        `);
        return;
    }

    patients.forEach(p => {
        const fullName = (p.firstName + ' ' + p.lastName).trim();
        const gender = p.gender ? p.gender.charAt(0).toUpperCase() + p.gender.slice(1) : '—';
        const dob = p.dateOfBirth ? p.dateOfBirth.split('T')[0] : '—';
        const dept = p.department ? p.department.departmentName : '—';
        const statusBadge = p.status === 'active' ? 'success' : (p.status === 'discharged' ? 'secondary' : 'dark');

        const tr = `
            <tr>
                <td>#${p.patientID}</td>
                <td><strong>${escapeHtml(fullName)}</strong></td>
                <td>${gender}</td>
                <td>${dob}</td>
                <td>${escapeHtml(p.phone || '—')}</td>
                <td>${escapeHtml(p.email || '—')}</td>
                <td><span class="badge badge-secondary">${escapeHtml(dept)}</span></td>
                <td><span class="badge badge-${statusBadge}">${p.status ? p.status.toUpperCase() : 'ACTIVE'}</span></td>
                <td style="text-align:right;">
                    <button class="btn btn-secondary btn-sm" onclick="editPatient(${p.patientID})" title="Edit">✏️ Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="openDeletePatientModal(${p.patientID}, '${escapeHtml(fullName)}')">🗑️ Delete</button>
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function renderPatientPagination(meta) {
    const container = $('#patientPagination');
    container.empty();

    if (meta.total <= meta.per_page) return;

    const current = meta.current_page;
    const last = meta.last_page;

    let html = `<span style="font-size:13px; color:var(--text-muted);">Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total} patients</span>`;
    html += `<div style="display:flex; gap:6px;">`;

    if (current > 1) {
        html += `<button class="btn btn-secondary btn-sm" onclick="filterPatients(${current - 1})">Prev</button>`;
    }
    if (current < last) {
        html += `<button class="btn btn-secondary btn-sm" onclick="filterPatients(${current + 1})">Next</button>`;
    }
    html += `</div>`;

    container.html(html);
}

function openPatientModal() {
    $('#patientID').val(0);
    $('#patientForm')[0].reset();
    $('#patientModalTitle').text('Add New Patient');
    $('#modalAlertContainer').empty();
    $('#patientModal').css('display', 'flex');
}

function closePatientModal() {
    $('#patientModal').css('display', 'none');
}

function editPatient(id) {
    $('#modalAlertContainer').empty();
    $.getJSON('/api/patients/' + id)
    .done(function (res) {
        if (res.status === 'success' && res.data) {
            const p = res.data;
            $('#patientID').val(p.patientID);
            $('#firstName').val(p.firstName);
            $('#lastName').val(p.lastName);
            $('#gender').val(p.gender || 'male');
            $('#dateOfBirth').val(p.dateOfBirth ? p.dateOfBirth.split('T')[0] : '');
            $('#phone').val(p.phone || '');
            $('#email').val(p.email || '');
            $('#departmentID').val(p.departmentID || '');
            $('#status').val(p.status || 'active');
            $('#address').val(p.address || '');

            $('#patientModalTitle').text('Edit Patient #' + p.patientID);
            $('#patientModal').css('display', 'flex');
        } else {
            showAlert('patientAlertContainer', res.message || 'Error fetching patient details', 'danger');
        }
    })
    .fail(function () {
        showAlert('patientAlertContainer', 'Failed to fetch patient details', 'danger');
    });
}

function savePatient(e) {
    e.preventDefault();
    const btn = $('#btnSavePatient');
    const originalText = btn.text();
    btn.prop('disabled', true).text('Saving...');

    const patientID = $('#patientID').val();
    const data = {
        patientID: patientID,
        firstName: $('#firstName').val(),
        lastName: $('#lastName').val(),
        gender: $('#gender').val(),
        dateOfBirth: $('#dateOfBirth').val(),
        phone: $('#phone').val(),
        email: $('#email').val(),
        departmentID: $('#departmentID').val(),
        status: $('#status').val(),
        address: $('#address').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    const url = patientID > 0 ? '/api/patients/' + patientID : '/api/patients';
    const method = patientID > 0 ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json'
    })
    .done(function (res) {
        if (res.status === 'success') {
            closePatientModal();
            showAlert('patientAlertContainer', res.message, 'success');
            filterPatients(currentPage);
        } else {
            showAlert('modalAlertContainer', res.message || 'Error saving patient', 'danger');
        }
    })
    .fail(function (xhr) {
        let msg = 'Validation error occurred';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
        }
        showAlert('modalAlertContainer', msg, 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text(originalText);
    });
}

function openDeletePatientModal(id, name) {
    deleteTargetId = id;
    $('#deletePatientName').text(name);
    $('#deletePatientModal').css('display', 'flex');
}

function closeDeletePatientModal() {
    deleteTargetId = null;
    $('#deletePatientModal').css('display', 'none');
}

function confirmDeletePatient() {
    if (!deleteTargetId) return;
    const btn = $('#btnConfirmDeletePatient');
    btn.prop('disabled', true).text('Deleting...');

    $.ajax({
        url: '/api/patients/' + deleteTargetId,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json'
    })
    .done(function (res) {
        closeDeletePatientModal();
        if (res.status === 'success') {
            showAlert('patientAlertContainer', res.message, 'success');
            filterPatients(currentPage);
        } else {
            showAlert('patientAlertContainer', res.message || 'Error deleting patient', 'danger');
        }
    })
    .fail(function () {
        closeDeletePatientModal();
        showAlert('patientAlertContainer', 'Failed to delete patient', 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text('Delete');
    });
}

// Helper: Alert notification box
function showAlert(containerId, message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const html = `
        <div class="alert ${alertClass} animate-in" style="margin-bottom:15px; padding:12px; border-radius:6px;">
            ${escapeHtml(message)}
        </div>
    `;
    $('#' + containerId).html(html);
    setTimeout(() => {
        $('#' + containerId + ' .alert').fadeOut(500, function () { $(this).remove(); });
    }, 4000);
}

// Helper: XSS prevention
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
