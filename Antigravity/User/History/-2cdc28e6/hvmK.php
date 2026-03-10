@extends('layout.base')

@section('title')
Students Import Management
@endsection

@section('head')
    <link rel="stylesheet" href="{{ asset('css/app/admin_panel/user_management/custom_profile.css') }}">
@endsection

@section('nav_title')
Students Import Management
@endsection

@section('body')
<div class="container-fluid">
    <div class="content-container">
        <!-- Page Header -->
        <x-table.page-header title="" subtitle="Manage system student imports">
            <button class="btn btn-primary" data-bs-toggle="offcanvas" id="btn-add" data-bs-target="#student-import-create-modal">
                <i class="fa-solid fa-plus fa-1x me-2"></i>
                Import Students
            </button>
        </x-table.page-header>

        
        <!-- DataTable -->
        <x-table.table id="studentImportsTable">
            {{-- Columns --}}
            <th>Id</th>
            <th>Filename</th>
            <th>Valid Rows</th>
            <th>Invalid Rows</th>
            <th>Total Rows</th>
            <th>Status</th>
            <th>Processed At</th>
            <th>Actions</th>
        </x-table.table>
                  
        @include('app.admin_panel.student_import_management.create_form')
        @include('app.admin_panel.student_import_management.update_form')
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/shared/generic-datatable.js') }}"></script>
<script src="{{ asset('js/shared/generic-crud.js') }}"></script>

<script>
$(document).ready(function() {

    // Initialize DataTable
    const studentImportsTable = new GenericDataTable({
        tableId: 'studentImportsTable',
        ajaxUrl: "{{ route('students.import.data') }}",
        columns: [
            { data: "id", visible: false },
            {
                data: "filename",
                render: (data, type, row) => {
                    const studentImportDownloadUrl = "{{ route('students.import.download', ':id') }}".replace(':id', row.id);

                    return `
                            <a href="${studentImportDownloadUrl}" class="${row.invalid_rows > 0 ? 'text-danger' : 'text-primary'} hover-underline-ltr" title="Download CSV">
                                <i class="fa-solid fa-file-excel me-1"></i>${data}
                            </a>
                        `;
                }
            },
            { data: "valid_rows", className: "none" },
            { data: "invalid_rows", className: "none" },
            { data: "total_rows" },
            {
                data: "status",
                render: (data, type, row) => {
                    const badge = (data === 'committed') ? 'success' : 'warning';

                    return `<span class="badge bg-label-${badge}">${data.toUpperCase()}</span>`;
                }
            },
            { data: "processed_at", className: "none" },
            { 
                data: null,
                orderable: false,
                render: (data, type, row) => {
                    const studentImportUrl = "{{ route('students.import.rows.index', ':id') }}".replace(':id', row.id);

                    return `
                        <a href="${studentImportUrl}" class="btn btn-sm btn-outline-info" title="Manage student import rows">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-warning" title="Edit" onclick="studentImportCRUD.edit('${row.id}')">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="studentImportCRUD.delete('${row.id}', '${row.filename}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    }).init();

    // CRUD Operations
    window.studentImportCRUD = new GenericCRUD({
        baseUrl: '/admin/students/import',
        storeUrl: "{{ route('students.import.store') }}",
        editUrl: "{{ route('students.import.edit', ':id') }}",
        updateUrl: "{{ route('students.import.update', ':id') }}",
        destroyUrl: "{{ route('students.import.destroy', ':id') }}",

        entityName: 'Student Import',
        dataTable: studentImportsTable,
        csrfToken: "{{ csrf_token() }}",
        form: '#student-import-update-form',
        modal: '#student-import-update-modal'
    });

    // Create form submission
    $('#student-import-create-form').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);

        studentImportCRUD.form = '#student-import-create-form';
        studentImportCRUD.modal = '#student-import-create-modal';

        studentImportCRUD.$form = $(studentImportCRUD.form);
        studentImportCRUD.$modal = $(studentImportCRUD.modal);

        studentImportCRUD.create(fd);
    });

    // Update form submission
    $('#student-import-update-form').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const id = $(this).find('input[name="id"]').val();

        studentImportCRUD.form = '#student-import-update-form';
        studentImportCRUD.modal = '#student-import-update-modal';

        studentImportCRUD.$form = $(studentImportCRUD.form);
        studentImportCRUD.$modal = $(studentImportCRUD.modal);

        fd.append('_method', 'PUT');
        studentImportCRUD.update(id, fd);
    });

    // Populate form on edit
    studentImportCRUD.onEditSuccess = (data) => {
        $('#student-import-update-form input[name="id"]').val(data.id);
        $('#student-import-update-form input[name="filename"]').val(data.filename);
    };

    // Reset form on modal close
    $('#student-import-create-modal').on('hidden.bs.offcanvas', function() {
        $('#student-import-create-form')[0].reset();

        studentImportCRUD.form = '#student-import-update-form';
        studentImportCRUD.modal = '#student-import-update-modal';

        studentImportCRUD.$form = $(studentImportCRUD.form);
        studentImportCRUD.$modal = $(studentImportCRUD.modal);
    });

    // Reset form on modal close
    $('#student-import-update-modal').on('hidden.bs.offcanvas', function() {
        $('#student-import-update-form')[0].reset();

        studentImportCRUD.form = '#student-import-update-form';
        studentImportCRUD.modal = '#student-import-update-modal';

        studentImportCRUD.$form = $(studentImportCRUD.form);
        studentImportCRUD.$modal = $(studentImportCRUD.modal);
    });

    
});
</script>
@endsection
