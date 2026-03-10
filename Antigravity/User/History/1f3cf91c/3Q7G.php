@extends('layout.base')

@section('title')
Grade Import History
@endsection

@section('nav_title')
ADMIN REPORTS
@endsection

@section('head')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.bootstrap5.css">
@endsection

@section('body')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Reports /</span> Grade Import History</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="importsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Uploader</th>
                            <th>Academic Period</th>
                            <th>File Name</th>
                            <th>Row Stats</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#importsTable').DataTable({
            processing: true,
            serverSide: true,
            order: [
                [0, 'desc']
            ],
            ajax: {
                url: "{{ route('admin.reports.imports.data') }}"
            },
            columns: [{
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'uploader',
                    name: 'user.name'
                },
                {
                    data: 'period',
                    name: 'academic_period.name'
                },
                {
                    data: 'filename',
                    name: 'filename'
                },
                {
                    data: 'stats',
                    name: 'stats',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            buttons: [{
                    extend: 'pdfHtml5',
                    text: '<i class="fa-solid fa-file-pdf me-1"></i> Export PDF',
                    className: 'btn btn-danger btn-sm mb-3',
                    title: 'Grade Import History'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Export Excel',
                    className: 'btn btn-success btn-sm mb-3',
                    title: 'Grade Import History'
                }
            ]
        });
    });
</script>
@endsection