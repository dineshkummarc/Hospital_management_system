/**
 * department.js
 * Clean AJAX logic for Department module.
 */

let deptDeleteTargetId = null;
let deptDebounceTimer = null;

$(document).ready(function () {
    filterDepartments();
});

function debounceFilterDepartments() {
    clearTimeout(deptDebounceTimer);
    deptDebounceTimer = setTimeout(() => filterDepartments(), 300);
}

function filterDepartments() {
    const search = $('#deptSearchInput').val();
    const status = $('#deptStatusFilter').val();

    $('#deptLoadingSpinner').show();
    $('#deptTableContainer').css('opacity', '0.5');

    $.getJSON('/api/departments', {
        search: search,
        status: status
    })
    .done(function (res) {
        if (res.status === 'success') {
            renderDepartmentTable(res.data);
        } else {
            showDeptAlert('deptAlertContainer', res.message || 'Failed to load departments', 'danger');
        }
    })
    .fail(function () {
        showDeptAlert('deptAlertContainer', 'Error connecting to server', 'danger');
    })
    .always(function () {
        $('#deptLoadingSpinner').hide();
        $('#deptTableContainer').css('opacity', '1');
    });
}

function renderDepartmentTable(departments) {
    const tbody = $('#deptTableBody');
    tbody.empty();

    if (!departments || departments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No departments found matching the search criteria.
                </td>
            </tr>
        `);
        return;
    }

    departments.forEach(d => {
        const docCount = d.doctorCount !== undefined ? d.doctorCount : (d.doctors_count || 0);
        const patCount = d.patientCount !== undefined ? d.patientCount : (d.patients_count || 0);
        const statusBadge = d.status === 'active' ? 'success' : 'secondary';

        const tr = `
            <tr>
                <td>#${d.departmentID}</td>
                <td><strong>${escapeDeptHtml(d.departmentName)}</strong></td>
                <td style="color:var(--text-muted); font-size:12px;">${escapeDeptHtml(d.description || '—')}</td>
                <td><span class="badge badge-info">${docCount} Doctors</span></td>
                <td><span class="badge badge-blue">${patCount} Patients</span></td>
                <td><span class="badge badge-${statusBadge}">${d.status ? d.status.toUpperCase() : 'ACTIVE'}</span></td>
                <td style="text-align:right;">
                    <button class="btn btn-secondary btn-sm" onclick="editDepartment(${d.departmentID})" title="Edit">✏️ Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="openDeleteDeptModal(${d.departmentID}, '${escapeDeptHtml(d.departmentName)}')">🗑️ Delete</button>
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function openDeptModal() {
    $('#departmentID').val(0);
    $('#deptForm')[0].reset();
    $('#deptModalTitle').text('Add New Department');
    $('#deptModalAlertContainer').empty();
    $('#deptModal').css('display', 'flex');
}

function closeDeptModal() {
    $('#deptModal').css('display', 'none');
}

function editDepartment(id) {
    $('#deptModalAlertContainer').empty();
    $.getJSON('/api/departments/' + id)
    .done(function (res) {
        if (res.status === 'success' && res.data) {
            const d = res.data;
            $('#departmentID').val(d.departmentID);
            $('#departmentName').val(d.departmentName);
            $('#description').val(d.description || '');
            $('#deptStatus').val(d.status || 'active');

            $('#deptModalTitle').text('Edit Department #' + d.departmentID);
            $('#deptModal').css('display', 'flex');
        } else {
            showDeptAlert('deptAlertContainer', res.message || 'Error fetching department details', 'danger');
        }
    })
    .fail(function () {
        showDeptAlert('deptAlertContainer', 'Failed to fetch department details', 'danger');
    });
}

function saveDepartment(e) {
    e.preventDefault();
    const btn = $('#btnSaveDept');
    const originalText = btn.text();
    btn.prop('disabled', true).text('Saving...');

    const departmentID = $('#departmentID').val();
    const data = {
        departmentID: departmentID,
        departmentName: $('#departmentName').val(),
        description: $('#description').val(),
        status: $('#deptStatus').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    const url = departmentID > 0 ? '/api/departments/' + departmentID : '/api/departments';
    const method = departmentID > 0 ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json'
    })
    .done(function (res) {
        if (res.status === 'success') {
            closeDeptModal();
            showDeptAlert('deptAlertContainer', res.message, 'success');
            filterDepartments();
        } else {
            showDeptAlert('deptModalAlertContainer', res.message || 'Error saving department', 'danger');
        }
    })
    .fail(function (xhr) {
        let msg = 'Validation error occurred';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
        }
        showDeptAlert('deptModalAlertContainer', msg, 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text(originalText);
    });
}

function openDeleteDeptModal(id, name) {
    deptDeleteTargetId = id;
    $('#deleteDeptName').text(name);
    $('#deleteDeptModal').css('display', 'flex');
}

function closeDeleteDeptModal() {
    deptDeleteTargetId = null;
    $('#deleteDeptModal').css('display', 'none');
}

function confirmDeleteDepartment() {
    if (!deptDeleteTargetId) return;
    const btn = $('#btnConfirmDeleteDept');
    btn.prop('disabled', true).text('Deleting...');

    $.ajax({
        url: '/api/departments/' + deptDeleteTargetId,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json'
    })
    .done(function (res) {
        closeDeleteDeptModal();
        if (res.status === 'success') {
            showDeptAlert('deptAlertContainer', res.message, 'success');
            filterDepartments();
        } else {
            showDeptAlert('deptAlertContainer', res.message || 'Error deleting department', 'danger');
        }
    })
    .fail(function () {
        closeDeleteDeptModal();
        showDeptAlert('deptAlertContainer', 'Failed to delete department', 'danger');
    })
    .always(function () {
        btn.prop('disabled', false).text('Delete');
    });
}

function showDeptAlert(containerId, message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const html = `
        <div class="alert ${alertClass} animate-in" style="margin-bottom:15px; padding:12px; border-radius:6px;">
            ${escapeDeptHtml(message)}
        </div>
    `;
    $('#' + containerId).html(html);
    setTimeout(() => {
        $('#' + containerId + ' .alert').fadeOut(500, function () { $(this).remove(); });
    }, 4000);
}

function escapeDeptHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
