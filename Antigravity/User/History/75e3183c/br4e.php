@extends('layout.base')

@section('title')
Officer Dashboard
@endsection

@section('head')
<link rel="stylesheet" href="{{ asset('css/app/dashboard.css') }}">
@endsection

@section('nav_title')
DASHBOARD
@endsection

@section('body')
<div class="container-fluid">

    {{-- Department Header + Current Academic Period --}}
    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card period-info-card h-100">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="period-label">Your Department</div>
                        <div class="period-value" id="departmentName">
                            <span class="stat-value-loading"></span>
                        </div>
                    </div>
                    <i class="fa-solid fa-building-user"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card period-info-card h-100">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="period-label">Current Academic Period</div>
                        <div class="period-value" id="currentPeriod">
                            <span class="stat-value-loading"></span>
                        </div>
                    </div>
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row dashboard-stats-row mb-4">
        <div class="col-sm-6 mb-3 mb-sm-0">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-label-primary">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="totalStudents"><span class="stat-value-loading"></span></div>
                        <div class="stat-label">Department Students</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-label-success">
                        <i class="fa-solid fa-table-list"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="totalPrograms"><span class="stat-value-loading"></span></div>
                        <div class="stat-label">Department Programs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fa-solid fa-chart-bar me-2"></i>Students per Program
                    </h6>
                    <canvas id="studentsByProgramChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fa-solid fa-chart-pie me-2"></i>Students by Year Level
                    </h6>
                    <canvas id="studentsByYearChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Grade Distribution --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fa-solid fa-star me-2"></i>Grade Distribution
                    </h6>
                    <canvas id="gradeDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    $(document).ready(function() {

        var colors = [
            '#696cff', '#8592ad', '#71dd37', '#ff3e1d', '#03c3ec',
            '#ffab00', '#9055fd', '#e7515a', '#2196f3', '#4caf50'
        ];

        $.get("{{ route('officer.dashboard.stats') }}", function(data) {

            // ── Stats Cards ──
            $('#departmentName').text(data.departmentName);
            $('#currentPeriod').text(data.currentPeriod);
            $('#totalStudents').text(data.totalStudents);
            $('#totalPrograms').text(data.totalPrograms);

            // ── Students by Program Chart ──
            var progLabels = data.studentsByProgram.map(function(p) {
                return p.label;
            });
            var progData = data.studentsByProgram.map(function(p) {
                return p.count;
            });

            new Chart(document.getElementById('studentsByProgramChart'), {
                type: 'bar',
                data: {
                    labels: progLabels,
                    datasets: [{
                        label: 'Students',
                        data: progData,
                        backgroundColor: colors.slice(0, progLabels.length),
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // ── Students by Year Level Chart ──
            var yearLabels = data.studentsByYear.map(function(y) {
                return y.label;
            });
            var yearData = data.studentsByYear.map(function(y) {
                return y.count;
            });

            new Chart(document.getElementById('studentsByYearChart'), {
                type: 'doughnut',
                data: {
                    labels: yearLabels,
                    datasets: [{
                        data: yearData,
                        backgroundColor: colors.slice(0, yearLabels.length),
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '60%',
                }
            });

            // ── Grade Distribution Chart ──
            var gradeLabels = data.gradeDistribution.map(function(g) {
                return g.label;
            });
            var gradeData = data.gradeDistribution.map(function(g) {
                return g.count;
            });
            var gradeColors = ['#71dd37', '#696cff', '#ffab00', '#03c3ec', '#ff3e1d'];

            new Chart(document.getElementById('gradeDistributionChart'), {
                type: 'bar',
                data: {
                    labels: gradeLabels,
                    datasets: [{
                        label: 'Grades',
                        data: gradeData,
                        backgroundColor: gradeColors,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

        }).fail(function() {
            toastr.error('Failed to load dashboard data.');
        });

    });
</script>
@endsection