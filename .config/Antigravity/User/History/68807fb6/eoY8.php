@extends('layout.base')

@section('title')
Grade Performance Report
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
        <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Reports /</span> Grade Performance</h4>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-3">Filters</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-uppercase">Program</label>
                    <select id="filterProgram" class="form-select">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-uppercase">School Year (e.g. 2023-2024)</label>
                    <input type="text" id="filterSY" class="form-control" placeholder="All">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-uppercase">Semester</label>
                    <select id="filterSem" class="form-select">
                        <option value="">All Semesters</option>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">Summer</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body mt-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="gradesTable">
                    <thead>
                        <tr>
                            <th>Student No.</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Subject</th>
                            <th>S.Y. / Sem</th>
                            <th>Grade</th>
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
        var table = $('#gradesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.reports.grades.data') }}",
                data: function(d) {
                    d.program_id = $('#filterProgram').val();
                    d.school_year = $('#filterSY').val();
                    d.semester = $('#filterSem').val();
                }
            },
            columns: [{
                    data: 'student_no',
                    name: 'student.student_number'
                },
                {
                    data: 'student_name',
                    name: 'student.user.name'
                },
                {
                    data: 'program_code',
                    name: 'student.program.code'
                },
                {
                    data: 'subject_code',
                    name: 'subject_code'
                },
                {
                    data: null,
                    name: 'school_year',
                    render: function(data) {
                        return data.school_year + ' / Sem ' + data.semester;
                    }
                },
                {
                    data: 'grade',
                    name: 'grade'
                },
                {
                    data: 'status',
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
                    title: 'Grade Performance Report'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Export Excel',
                    className: 'btn btn-success btn-sm mb-3',
                    title: 'Grade Performance Report'
                }
            ]
        });

        $('#filterProgram, #filterSem').on('change', function() {
            table.draw();
        });

        $('#filterSY').on('keyup', function() {
            table.draw();
        });
    });
</script>
@endsection