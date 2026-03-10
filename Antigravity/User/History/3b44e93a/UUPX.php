@extends('layout.base')

@section('title')
{{ $studentImportName }} - Student Import Data Management
@endsection

@section('head')
    <link rel="stylesheet" href="{{ asset('css/app/admin_panel/user_management/custom_profile.css') }}">
@endsection

@section('nav_title')
{{ $studentImportName }} - Student Import Data Management
@endsection

@section('body')
<div class="container-fluid">
    <div class="content-container">
        <!-- Page Header -->
        <x-table.page-header
            title=""
            subtitle="Manage student import data"
            showBackButton="true"
            backUrl="{{ route('students.import.index') }}">

            <button class="btn btn-primary" data-bs-toggle="offcanvas" id="btn-add" data-bs-target="#add-or-update-modal">
                <i class="fa-solid fa-plus fa-1x me-2"></i>
                Add New Data
            </button>
        </x-table.page-header>

        <!-- Statistics Cards (Optional) -->
        <div class="row">

        </div>

        <div class="text-end">
            <a class="btn btn-outline-primary text-primary" data-bs-toggle="offcanvas" id="btn-import" data-bs-target="#import-modal">
                <i class="fa-solid fa-file-import fa-1x me-2"></i>
                Import CSV
            </a>
            <a class="btn btn-outline-success" href="{{ route('students.import.download', $studentImportId) }}" role="button">
                <i class="fa-solid fa-file-export fa-1x me-2"></i>
                Export CSV
            </a>
        </div>

        <div class="alert alert-danger mt-3" id="invalid-records-alert-alt" role="alert" style="display: none;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            There are invalid student import records that need to be addressed before committing. Please review and correct the errors.
        </div>

        <!-- Status Filter -->
        <div class="row">
            <div class="col-md-2">
                <x-input.select-field
                    id="filter-status"
                    label="Filter by Status:"
                    icon="fa-solid fa-tags"
                    :options="[
                        ['value' => 'All', 'text' => 'All Status'],
                        ['value' => 'staged', 'text' => 'Staged'],
                        ['value' => 'committed', 'text' => 'Committed'],
                    ]"
                    placeholder="Select Status"
                />
            </div>
            <div class="col-md-2">
                <x-input.select-field
                    id="filter-validity"
                    label="Filter by Validity:"
                    icon="fa-solid fa-tags"
                    :options="[
                        ['value' => 'All', 'text' => 'All Validity'],
                        ['value' => 'valid', 'text' => 'Valid'],
                        ['value' => 'invalid', 'text' => 'Invalid'],
                    ]"
                    placeholder="Select Validity"
                />
            </div>
        </div>

        <!-- DataTable -->
        <x-table.table id="studentImportRowsTable">
            {{-- Columns --}}
            <th>Id</th>
            <th>Student No.</th>
            <th>Name</th>
            <th>Program Code</th>
            <th>Program Name</th>
            <th>Year Level</th>
            <th>Validity</th>
            <th>Status</th>
            <th>Actions</th>
        </x-table.table>

        <div class="container" id="commit-section-alt" style="display: none;">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Commit and Register?</h6>
                            <small class="text-muted">Commit and register the staged student data into the system?</small>
                        </div>
                        <div>
                            <button class="btn btn-success" id="btn-commit" onclick="commitAll()">
                                <i class="fa-solid fa-check me-2"></i>
                                Commit Records
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" id="uncommitAll-section-alt" style="display: none;">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Uncommit all?</h6>
                            <small class="text-muted">Uncommit all staged student data and remove the registered students?</small>
                        </div>
                        <div>
                            <button class="btn btn-danger" id="btn-uncommit" onclick="uncommitAll()">
                                <i class="fa-solid fa-times me-2"></i>
                                Uncommit Records
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('app.admin_panel.student_import_management.student_import_rows.form')
        @include('app.admin_panel.student_import_management.student_import_rows.modal')
        @include('app.admin_panel.student_import_management.student_import_rows.import_form')
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/shared/generic-datatable.js') }}"></script>
<script src="{{ asset('js/shared/generic-crud.js') }}"></script>
<script src="{{ asset('js/shared/select2-init.js') }}"></script>
<script>
$(document).ready(function() {

    @if ($allCommited)
        $('#uncommitAll-section-alt').show();
    @endif

    $('#filter-status').select2({
        minimumResultsForSearch: -1,
        placeholder: 'All Status'
    });

    $('#filter-validity').select2({
        minimumResultsForSearch: -1,
        placeholder: 'All Validity'
    });

    @if ($hasStagedData && $valid)
        $('#commit-section-alt').show();
    @elseif (!$valid)
        $('#invalid-records-alert-alt').show();
    @endif

    // Initialize DataTable
    window.studentImportRowsTable = new GenericDataTable({
        order: [[2, 'desc']],
        tableId: 'studentImportRowsTable',
        ajaxUrl: "{{ route('students.import.rows.data', $studentImportId) }}",
        ajaxData: function(d) {
            d.status = $('#filter-status').val();
            d.validity = $('#filter-validity').val();
        },
        columns: [
            { data: "id", visible: false },
            { data: "student_number" },
            { data: "name" },
            { data: "program_code" },
            { data: "program_name", className: "none" },
            { data: "year_level", className: "none" },
            {
                data: "validity",
                render: (data, type, row) => {
                    const badge = (data === 'valid') ? 'success' : 'danger';

                    return `<span class="badge bg-label-${badge}">${data}</span>`;
                }
            },
            {
                data: "status",
                render: (data, type, row) => {
                    const badge = (data === 'committed') ? 'success' : 'warning';

                    return `<span class="badge bg-label-${badge}">${data}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                responsivePriority: 1,
                render: (data, type, row) => {
                    return `
                        ${row.validity === 'invalid' ? `<button class="btn btn-sm btn-outline-primary" title="See errors" onclick="openErrorModal('${row.id}')">
                            <i class="fa-solid fa-circle-question"></i>
                        </button>` : ''}

                        ${row.status === 'committed' ? `<button class="btn btn-sm btn-outline-secondary" title="Uncommit data for: ${row.student_number}" onclick="unCommit('${row.id}')">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>` : ''}

                        ${row.status === 'staged' ? `
                            <button class="btn btn-sm btn-outline-warning" title="Edit data for: ${row.student_number}" onclick="studentImportRowsCRUD.edit('${row.id}')">
                                <i class="fa-solid fa-pencil"></i>
                            </button>

                            <button class="btn btn-sm btn-outline-danger" title="Delete data for: ${row.student_number}" onclick="studentImportRowsCRUD.delete('${row.id}', 'student record for: ${row.student_number}')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        ` : ''}
                    `;
                }
            }
        ],
        statsCards: {
            callback: (table) => {
            }
        }
    }).init();

    window.studentImportRowsCRUD = new GenericCRUD({
        baseUrl: '/admin/students/import/rows',
        storeUrl: "{{ route('students.import.rows.store', $studentImportId) }}",
        editUrl: "{{ route('students.import.rows.edit', ':id') }}",
        updateUrl: "{{ route('students.import.rows.update', ':id') }}",
        destroyUrl: "{{ route('students.import.rows.destroy', ':id') }}",

        entityName: 'Student Import Data',
        dataTable: window.studentImportRowsTable,
        csrfToken: "{{ csrf_token() }}",
        form: '#add-or-update-form',
        modal: '#add-or-update-modal'
    });

    $('#add-or-update-form').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const id = $(this).find('input[name="id"]').val();

        if (id) {
            fd.append('_method', 'PUT');
            studentImportRowsCRUD.update(id, fd);
        } else {
            studentImportRowsCRUD.create(fd);
        }
    });

    studentImportRowsCRUD.onEditSuccess = (data) => {
        $('#add-or-update-form input[name="id"]').val(data.id);
        $('#add-or-update-form input[name="student_number"]').val(data.student_number);
        $('#add-or-update-form input[name="name"]').val(data.name);
        $('#add-or-update-form input[name="program_code"]').val(data.program_code);
    };

    $('#add-or-update-modal').on('hidden.bs.offcanvas', function() {
        $('#add-or-update-form')[0].reset();
    });

    studentImportRowsCRUD.onCreateSuccess = (data) => {
        if (data.allValid && data.allCommited) {
            $('#invalid-records-alert-alt').remove();
            $('#commit-section-alt').remove();
            $('#uncommitAll-section-alt').show();
        }
        else if (data.allValid && !data.allCommited) {
            $('#invalid-records-alert-alt').remove();
            $('#uncommitAll-section-alt').hide();
            $('#commit-section-alt').show();
        }
        else {
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#invalid-records-alert-alt').show();
        }
    };

    studentImportRowsCRUD.onUpdateSuccess = (data) => {
        if (data.allValid && data.allCommited) {
            $('#invalid-records-alert-alt').hide();
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').show();
        }
        else if (data.allValid && !data.allCommited) {
            $('#invalid-records-alert-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#commit-section-alt').show();
        }
        else {
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#invalid-records-alert-alt').show();
        }
    };

    studentImportRowsCRUD.onDeleteSuccess = (data) => {
        if (data.allValid && data.allCommited) {
            $('#invalid-records-alert-alt').hide();
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').show();
        }
        else if (data.allValid && !data.allCommited) {
            $('#invalid-records-alert-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#commit-section-alt').show();
        }
        else {
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#invalid-records-alert-alt').show();
        }
    };

    window.Import = new GenericCRUD({
        baseUrl: '/admin/students/import/rows',
        storeUrl: "{{ route('students.import.rows.import', $studentImportId) }}",

        entityName: 'Student Import Data',
        csrfToken: "{{ csrf_token() }}",
        form: '#student-import-form',
        modal: '#import-modal'
    });

    $('#student-import-form').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);

        Import.create(fd);
    });

    Import.onCreateSuccess = (data) => {
        if (data.allValid && data.allCommited) {
            $('#invalid-records-alert-alt').hide();
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').show();
        }
        else if (data.allValid && !data.allCommited) {
            $('#invalid-records-alert-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#commit-section-alt').show();
        }
        else {
            $('#commit-section-alt').hide();
            $('#uncommitAll-section-alt').hide();
            $('#invalid-records-alert-alt').show();
        }
    };

    $('#filter-status').on('change', function() {
        studentImportRowsTable.reload();
    });
    $('#filter-validity').on('change', function() {
        studentImportRowsTable.reload();
    });

});

function openErrorModal(rowId) {
    $('#modal').modal('show');

    populateErrorMessages(rowId);
}

function populateErrorMessages(rowId) {
    $.get("{{ route('students.import.rows.errors', ':id') }}".replace(':id', rowId), function (data) {
        const container = $('#error-messages-container');
        container.empty();

        if (!data.messages) {
            container.append(
                '<div class="alert alert-info">No errors found.</div>'
            );
            return;
        }

        let messages = [];
        let parsedMessages = data.messages;

        if (typeof data.messages === 'string') {
            try {
                parsedMessages = JSON.parse(data.messages);
            } catch (e) {
                parsedMessages = data.messages;
            }
        }

        if (Array.isArray(parsedMessages)) {
            parsedMessages.forEach(item => {
                if (Array.isArray(item)) {
                    messages.push(...item);
                } else {
                    messages.push(item);
                }
            });
        } else if (typeof parsedMessages === 'object') {
            Object.values(parsedMessages).forEach(value => {
                if (Array.isArray(value)) {
                    messages.push(...value);
                } else {
                    messages.push(value);
                }
            });
        } else {
            messages.push(parsedMessages);
        }

        if (messages.length === 0) {
            container.append(
                '<div class="alert alert-info">No errors found.</div>'
            );
            return;
        }

        messages.forEach(msg => {
            container.append(
                `<div class="alert alert-danger" role="alert">${msg}</div>`
            );
        });
    });
}

function unCommit(rowId) {
    Swal.fire({
        title: 'Confirm Uncommit',
        html: `Are you sure you want to uncommit this student record?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#F8BB86",
        cancelButtonColor: "#91a8b3ff",
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('students.import.rows.uncommit', ':id') }}".replace(':id', rowId),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: (response) => {
                    toastr.success(response.message || "Student record has been uncommitted.");
                    window.studentImportRowsTable.reload();
                    $('#commit-section-alt').show();
                },
                error: (xhr) => {
                    if (xhr.status === 403) {
                        const msg = xhr.responseJSON?.message || 'Action forbidden';
                        toastr.error(msg, 'Forbidden');
                        return;
                    }

                    if (xhr.status === 500) {
                        const msg = xhr.responseJSON?.message || 'Internal server error';
                        toastr.error(msg, 'Server Error');
                        return;
                    }

                    toastr.error(xhr.responseJSON?.message || 'An error occurred while uncommitting the record.', 'Error');
                }
            });
        }
    });
}

function commitAll() {
    Swal.fire({
        title: 'Confirm Commit All',
        html: `Are you sure you want to commit all valid student import records? This will register the students into the system.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#F8BB86",
        cancelButtonColor: "#91a8b3ff",
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('students.import.rows.commitAll', $studentImportId) }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: (response) => {
                    toastr.success(response.message || "All valid student import records have been committed.");
                    $('#commit-section').hide();
                    $('#commit-section-alt').hide();
                    $('#invalid-records-alert').hide();
                    $('#invalid-records-alert-alt').hide();
                    $('#uncommitAll-section-alt').show();
                    window.studentImportRowsTable.reload();
                },
                error: (xhr) => {
                    if (xhr.status === 403) {
                        const msg = xhr.responseJSON?.message || 'Action forbidden';
                        toastr.error(msg, 'Forbidden');
                        return;
                    }

                    if (xhr.status === 500) {
                        const msg = xhr.responseJSON?.message || 'Internal server error';
                        toastr.error(msg, 'Server Error');
                        return;
                    }

                    toastr.error(xhr.responseJSON?.message || 'An error occurred while committing the records.', 'Error');
                }
            });
        }
    });
}

function uncommitAll() {
    Swal.fire({
        title: 'Confirm Uncommit All',
        html: `Are you sure you want to uncommit all student import records? This will remove the registered students.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#F8BB86",
        cancelButtonColor: "#91a8b3ff",
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('students.import.rows.uncommitAll', $studentImportId) }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: (response) => {
                    toastr.success(response.message || "All student import records have been uncommitted.");
                    $('#commit-section-alt').show();
                    $('#invalid-records-alert-alt').hide();
                    $('#uncommitAll-section-alt').hide();
                    window.studentImportRowsTable.reload();
                },
                error: (xhr) => {
                    if (xhr.status === 403) {
                        const msg = xhr.responseJSON?.message || 'Action forbidden';
                        toastr.error(msg, 'Forbidden');
                        return;
                    }

                    if (xhr.status === 500) {
                        const msg = xhr.responseJSON?.message || 'Internal server error';
                        toastr.error(msg, 'Server Error');
                        return;
                    }

                    toastr.error(xhr.responseJSON?.message || 'An error occurred while uncommitting the records.', 'Error');
                }
            });
        }
    });
}


</script>

@endsection
