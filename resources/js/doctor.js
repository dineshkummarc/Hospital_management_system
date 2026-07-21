/**
 * doctor.js
 * Clean AJAX logic for Doctor module.
 */

let doctorCurrentPage = 1;
let doctorDeleteTargetId = null;
let doctorDebounceTimer = null;

$(document).ready(function () {
    filterDoctors();
});

function debounceFilterDoctors() {
    clearTimeout(doctorDebounceTimer);
    doctorDebounceTimer = setTimeout(() => filterDoctors(1), 300);
}

function filterDoctors(page = 1) {
    doctorCurrentPage = page;
    const search = $('#doctorSearchInput').val();
    const status = $('#doctorStatusFilter').val();
    const departmentID = $('#doctorDepartmentFilter').val();

    $('#doctorLoadingSpinner').show();
    $('#doctorTableContainer').css('opacity', '0.5');

    $.getJSON('/api/doctors', {
        search: search,
        status: status,
        departmentID: departmentID,
        page: page
    })
    .done(function (res) {
        if (res.status === 'success') {
            renderDoctorTable(res.data.data);
            renderDoctorPagination(res.data);
        } else {
            showDocAlert('doctorAlertContainer', res.message || 'Failed to load doctors', 'danger');
        }
    })
    .fail(function () {
        showDocAlert('doctorAlertContainer', 'Error connecting to server', 'danger');
    })
    .always(function () {
        $('#doctorLoadingSpinner').hide();
        $('#doctorTableContainer').css('opacity', '1');
    });
}

function renderDoctorTable(doctors) {
    const tbody = $('#doctorTableBody');
    tbody.empty();

    if (!doctors || doctors.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No doctors found matching the search criteria.
                </td>
            </tr>
        `);
        return;
    }

    doctors.forEach(d => {
        const dept = d.department ? d.department.departmentName : '—';
        const statusBadge = d.status === 'active' ? 'success' : (d.status === 'on_leave' ? 'warning' : 'secondary');
        const statusText = d.status === 'on_leave' ? 'ON LEAVE' : (d.status ? d.status.toUpperCase() : 'ACTIVE');

        const tr = `
            <tr>
                <td>#${d.doctorID}</td>
                <td><strong>${escapeDocHtml(d.fullName)}</strong></td>
                <td>${escapeDocHtml(d.specialization || 'General')}</td>
                <td>${escapeDocHtml(d.email || '—')}</td>
                <td>${escapeDocHtml(d.phone || '—')}</td>
                <td><span class="badge badge-secondary">${escapeDocHtml(dept)}</span></td>
                <td><span class="badge badge-${statusBadge}">${statusText}</span></td>
                <td style="text-align:right;">
                    <button class="btn btn-secondary btn-sm" onclick="editDoctor(${d.doctorID})" title="Edit">✏️ Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="openDeleteDoctorModal(${d.doctorID}, '${escapeDocHtml(d.fullName)}')">🗑️ Delete</button>
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function renderDoctorPagination(meta) {
    const container = $('#doctorPagination');
    container.empty();

    if (meta.total <= meta.per_page) return;

    const current = meta.current_page;
    const last = meta.last_page;

    let html = `<span style="font-size:13px; color:var(--text-muted);">Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total} doctors</span>`;
    html += `<div style="display:flex; gap:6px;">`;

    if (current > 1) {
        html += `<button class="btn btn-secondary btn-sm" onclick="filterDoctors(${current - 1})">Prev</button>`;
    }
    if (current < last) {
        html += `<button class="btn btn-secondary btn-sm" onclick="filterDoctors(${current + 1})">Next</button>`;
    }
    html += `</div>`;

    container.html(html);
}

function openDoctorModal() {
    $('#doctorID').val(0);
    $('#doctorForm')[0].reset();
    $('#doctorModalTitle').text('Add New Doctor');
    $('#doctorModalAlertContainer').empty();
    $('#doctorModal').css('display', 'flex');
}

function closeDoctorModal() {
    $('#doctorModal').css('display', 'none');
}

function editDoctor(id) {
    $('#doctorModalAlertContainer').empty();
    $.getJSON('/api/doctors/' + id)
    .done(function (res) {
        if (res.status === 'success' && res.data) {
            const d = res.data;
            $('#doctorID').val(d.doctorID);
            $('#fullName').val(d.fullName);
            $('#specialization').val(d.specialization || '');
            $('#docDepartmentID').val(d.departmentID || '');
            $('#docEmail').val(d.email || '');
            $('#docPhone').val(d.phone || '');
            $('#docStatus').val(d.status || 'active');

            $('#doctorModalTitle').text('Edit Doctor #' + d.doctorID);
            $('#doctorModal').css('display', 'flex');
        } else {
            showDocAlert('doctorAlertContainer', res.message || 'Error fetching doctor details', 'danger');
        }
    })
    .fail(function () {
        showDocAlert('doctorAlertContainer', 'Failed to fetch doctor details', 'danger');
    });
}

function saveDoctor(e) {
    e.preventDefault();
    const btn = $('#btnSaveDoctor');
    const originalText = btn.text();
    btn.prop('disabled', true).text('Saving...');

    const doctorID = $('#doctorID').val();
    const data = {
        doctorID: doctorID,
        fullName: $('#fullName').val(),
        specialization: $('#specialization').val(),
        departmentID: $('#docDepartmentID').val(),
        email: $('#docEmail').val(),
        phone: $('#docPhone').val(),
        status: $('#docStatus').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    const url = doctorID > 0 ? '/api/doctors/' + doctorID : '/api/doctors';
    const method = doctorID > 0 ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json'
    })
    .done(function (res) {
        if (res.status === 'success') {
            closeDoctorModal();
            showDocAlert('doctorAlertContainer', res.message, 'success');
            filterDoctors(doctorCurrentPage);
        } else {
            showDocAlert('doctorModalAlertContainer', res.message || 'Error saving doctor', 'danger');
        }
    })
    .fail(function (xhr) {
        let msg = 'Validation error occurred';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
        }
        showDocAlert('doctorModalAlertContainer', msg, 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text(originalText);
    });
}

function openDeleteDoctorModal(id, name) {
    doctorDeleteTargetId = id;
    $('#deleteDoctorName').text(name);
    $('#deleteDoctorModal').css('display', 'flex');
}

function closeDeleteDoctorModal() {
    doctorDeleteTargetId = null;
    $('#deleteDoctorModal').css('display', 'none');
}

function confirmDeleteDoctor() {
    if (!doctorDeleteTargetId) return;
    const btn = $('#btnConfirmDeleteDoctor');
    btn.prop('disabled', true).text('Deleting...');

    $.ajax({
        url: '/api/doctors/' + doctorDeleteTargetId,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json'
    })
    .done(function (res) {
        closeDeleteDoctorModal();
        if (res.status === 'success') {
            showDocAlert('doctorAlertContainer', res.message, 'success');
            filterDoctors(doctorCurrentPage);
        } else {
            showDocAlert('doctorAlertContainer', res.message || 'Error deleting doctor', 'danger');
        }
    })
    .fail(function () {
        closeDeleteDoctorModal();
        showDocAlert('doctorAlertContainer', 'Failed to delete doctor', 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text('Delete');
    });
}

function showDocAlert(containerId, message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const html = `
        <div class="alert ${alertClass} animate-in" style="margin-bottom:15px; padding:12px; border-radius:6px;">
            ${escapeDocHtml(message)}
        </div>
    `;
    $('#' + containerId).html(html);
    setTimeout(() => {
        $('#' + containerId + ' .alert').fadeOut(500, function () { $(this).remove(); });
    }, 4000);
}

function escapeDocHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
