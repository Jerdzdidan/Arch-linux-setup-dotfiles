@extends('layout.base')

@section('title')
Student Accounts Management
@endsection

@section('head')
    <link rel="stylesheet" href="{{ asset('css/app/admin_panel/user_management/custom_profile.css') }}">
@endsection

@section('nav_title')
Student Accounts Management
@endsection

@section('body')
<div class="container-fluid">
    <div class="content-container">
        <!-- Page Header -->
        <x-table.page-header title="" subtitle="Manage system accounts">
            <button class="btn btn-outline-primary me-2" data-bs-toggle="offcanvas" data-bs-target="#import-students-modal">
                <i class="fa-solid fa-file-import fa-1x me-2"></i>
                Import Students
            </button>
            <button class="btn btn-primary" data-bs-toggle="offcanvas" id="btn-add" data-bs-target="#add-or-update-modal">
                <i class="fa-solid fa-plus fa-1x me-2"></i>
                Add New Account
            </button>
        </x-table.page-header>
        
        <!-- Statistics Cards (Optional) -->
        <div class="row mb-4">
            
            {{-- TOTAL STUDENTS --}}
            <x-table.stats-card 
                id="totalStudents" 
                title="Total Students" 
                icon="fa-solid fa-user fa-2x" 
                bgColor="bg-primary" 
                class="col-md-4"/>

            {{-- ACTIVE ACCOUNTS --}}
            <x-table.stats-card 
                id="activeStudents" 
                title="Active" 
                icon="fa-solid fa-user-check fa-2x" 
                bgColor="bg-success" 
                class="col-md-4"/>

            {{-- INACTIVE ACCOUNTS --}}
            <x-table.stats-card 
                id="inactiveStudents" 
                title="Inactive" 
                icon="fa-solid fa-user-xmark fa-2x" 
                bgColor="bg-danger" 
                class="col-md-4"/>

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
                        ['value' => 'Active', 'text' => 'Active'],
                        ['value' => 'Inactive', 'text' => 'Inactive'],
                    ]"
                    placeholder="Select Status"
                />
            </div>
        </div>
        
        
        <!-- DataTable -->
        <x-table.table id="studentAccountsTable">
            {{-- Columns --}}
            <th>Id</th>
            <th>Student No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Year Level</th>
            <th>Program</th>
            <th>Curriculum</th>
            <th>Status</th>
            <th>Actions</th>
        </x-table.table>

        @include('app.admin_panel.user_management.student_accounts.form')

        {{-- Import Students Modal --}}
        <x-modals.creation-and-update-modal
            id="import-students-modal"
            title="Import Students"
            action=""
            formId="import-students-form"
            submitButtonName="Import"
            enctype="multipart/form-data"
        >
            <div class="mb-3">
                <x-input.file-field
                    id="import-file"
                    label="CSV / Excel File"
                    name="file"
                    accept=".csv,.xlsx,.xls"
                    helptext="Required columns: student_number, name, program_code"
                />
            </div>
        </x-modals.creation-and-update-modal>

        {{-- Import Results Modal --}}
        <div class="modal fade" id="import-results-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Import Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="alert alert-success mb-0">
                                    <i class="fa-solid fa-check-circle me-2"></i>
                                    <strong>Imported:</strong> <span id="imported-count">0</span> student(s)
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-danger mb-0">
                                    <i class="fa-solid fa-times-circle me-2"></i>
                                    <strong>Failed:</strong> <span id="failed-count">0</span> row(s)
                                </div>
                            </div>
                        </div>
                        <div id="failed-rows-section" style="display: none;">
                            <h6 class="fw-bold mb-3">Failed Rows:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="failed-rows-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Row #</th>
                                            <th>Student No.</th>
                                            <th>Name</th>
                                            <th>Program Code</th>
                                            <th>Error(s)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/shared/generic-datatable.js') }}"></script>
<script src="{{ asset('js/shared/generic-crud.js') }}"></script>
<script src="{{ asset('js/shared/select2-init.js') }}"></script>
<script src="{{ asset('js/admin_panel/utils.js') }}"></script>
<script>
$(document).ready(function() {
    let curriculum_route = "{{ route('curricula.select', ':id') }}"

    $('#curriculum_id').select2({
        allowClear: true,
        placeholder: 'Select a curriculum'
    });

    $('#filter-status').select2({
        minimumResultsForSearch: -1,
        placeholder: 'All Status'
    });

    // Select2
    prefetchAndInitSelect2('#program_id', "{{ route('programs.select') }}", 'Select a program', '#add-or-update-modal');

    // Initialize DataTable
    const studentsTable = new GenericDataTable({
        tableId: 'studentAccountsTable',
        ajaxUrl: "{{ route('students.data') }}",
        ajaxData: function(d) {
            d.status = $('#filter-status').val();
        },
        columns: [
            { data: "id", visible: false },
            { data: "student_number" },
            { data: "user.name" },
            { 
                data: "user.email",
                defaultContent: '---'
            },
            { data: "year_level" },
            { data: "program.code" },
            { data: "curriculum" },
            { 
                data: "user.status",
                render: (data, type, row) => {
                    const status = row.user.status ? 'Active' : 'Inactive';
                    const badge = row.user.status ? 'success' : 'danger';
                    return `<span class="badge bg-label-${badge}">${status}</span>`;
                }
            },
            { 
                data: null,
                orderable: false,
                render: (data, type, row) => {
                    const toggleIcon = row.user.status
                        ? '<i class="fa-solid fa-toggle-on"></i>'
                        : '<i class="fa-solid fa-toggle-off"></i>';

                    return `
                        <button class="btn btn-sm btn-outline-primary" title="Toggle user status" onclick="studentCRUD.toggleStatus('${row.user_id}', '${row.user.name}')">
                            ${toggleIcon}
                        </button>

                        <button class="btn btn-sm btn-outline-warning" title="Edit user: ${row.user.name}" onclick="studentCRUD.edit('${row.id}')">
                            <i class="fa-solid fa-pencil"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-danger" title="Delete user: ${row.user.name}" onclick="studentCRUD.delete('${row.id}', '${row.user.name}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        statsCards: {
            callback: (table) => {
                $.get("{{ route('users.stats', 'STUDENT') }}", (data) => {
                    $('#totalStudents').text(data.total);
                    $('#activeStudents').text(data.active);
                    $('#inactiveStudents').text(data.inactive);
                });
            }
        }
    }).init();
    
    window.studentCRUD = new GenericCRUD({
        baseUrl: '/admin/users/',
        storeUrl: "{{ route('students.store') }}",
        editUrl: "{{ route('students.edit', ':id') }}",
        updateUrl: "{{ route('students.update', ':id') }}",
        destroyUrl: "{{ route('students.destroy', ':id') }}",
        toggleUrl: "{{ route('users.toggle', ':id') }}",

        entityName: 'Student',
        dataTable: studentsTable,
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
            studentCRUD.update(id, fd);
        } else {
            studentCRUD.create(fd);
        }
    });

    studentCRUD.onEditSuccess = async (data) => {
        $('#add-or-update-modal .offcanvas-body').css('position', 'relative').prepend(loadingOverlay);

        try {
            $('#add-or-update-form input[name="id"]').val(data.id);
            $('#add-or-update-form input[name="student_number"]').val(data.student_number);
            $('#add-or-update-form input[name="name"]').val(data.name);
            $('#add-or-update-form input[name="email"]').val(data.email);
            $('#add-or-update-form input[name="year_level"]').val(data.year_level);

            setSelect2Value('#program_id', data.program_id);
            
            $('#curriculum_id').prop('disabled', false);

            let url = curriculum_route.replace(':id', data.program_id);
            $('#curriculum_id').empty();
            await prefetchAndInitSelect2('#curriculum_id', url, 'Select a curriculum');
            setSelect2Value('#curriculum_id', data.curriculum_id);
        } finally {
            $('#loading-overlay').remove();
        }
    };

    $('#add-or-update-modal').on('hidden.bs.offcanvas', function() {
        $('#add-or-update-form')[0].reset();
        resetSelect2('#program_id');
        resetSelect2('#curriculum_id');
        $('#curriculum_id').prop('disabled', true);
    });

    $('#program_id').on('change', function() {
        const programId = $(this).val();
    
        if (programId) {
            $('#curriculum_id').prop('disabled', false);
            let url = curriculum_route.replace(':id', programId);
            $('#curriculum_id').empty();
            prefetchAndInitSelect2('#curriculum_id', url, 'Select a curriculum');
        } 
        else {
            $('#curriculum_id').prop('disabled', true);
            resetSelect2('#curriculum_id');
        }
    })

    $('#filter-status').on('change', function() {
        studentsTable.reload();
    });

    // Import Students
    $('#import-students-form').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();

        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Importing...');

        $.ajax({
            url: "{{ route('students.import') }}",
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(data) {
                // Close import modal
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('import-students-modal'));
                if (offcanvas) offcanvas.hide();
                $('#import-students-form')[0].reset();

                // Populate results
                $('#imported-count').text(data.imported_count);
                $('#failed-count').text(data.failed_count);

                const $tbody = $('#failed-rows-table tbody');
                $tbody.empty();

                if (data.failed_rows && data.failed_rows.length > 0) {
                    $('#failed-rows-section').show();
                    data.failed_rows.forEach(function(row) {
                        const errors = row.errors.join('<br>');
                        $tbody.append(`
                            <tr>
                                <td>${row.row}</td>
                                <td>${row.student_number || '—'}</td>
                                <td>${row.name || '—'}</td>
                                <td>${row.program_code || '—'}</td>
                                <td class="text-danger">${errors}</td>
                            </tr>
                        `);
                    });
                } else {
                    $('#failed-rows-section').hide();
                }

                // Show results modal
                new bootstrap.Modal(document.getElementById('import-results-modal')).show();

                // Refresh table
                studentsTable.reload();

                if (data.imported_count > 0) {
                    toastr.success(data.message);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Import failed';
                toastr.error(msg, 'Error');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

});
</script>

@endsection