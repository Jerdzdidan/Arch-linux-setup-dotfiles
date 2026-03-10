<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeImport;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    // ==========================================
    // Student Directory
    // ==========================================

    public function studentDirectory()
    {
        $programs = Program::all();
        return view('app.admin_panel.reports.student_directory', compact('programs'));
    }

    public function studentDirectoryData(Request $request)
    {
        $query = Student::with(['user', 'program', 'curriculum']);

        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->year_level) {
            $query->where('year_level', $request->year_level);
        }

        return DataTables::of($query)
            ->addColumn('student_name', function ($student) {
                return $student->user->name ?? '—';
            })
            ->addColumn('email', function ($student) {
                return $student->user->email ?? '—';
            })
            ->addColumn('program_code', function ($student) {
                return $student->program->code ?? '—';
            })
            ->addColumn('status', function ($student) {
                $status = $student->user->status ?? false;
                return $status
                    ? '<span class="badge bg-label-success">Active</span>'
                    : '<span class="badge bg-label-danger">Inactive</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    // ==========================================
    // Grade Performance
    // ==========================================

    public function gradePerformance()
    {
        $programs = Program::all();
        return view('app.admin_panel.reports.grade_performance', compact('programs'));
    }

    public function gradePerformanceData(Request $request)
    {
        // Get all graded entries (passed or failed) joined with student info
        $query = Grade::with(['student.program'])
            ->whereNotNull('grade');

        if ($request->program_id) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->semester) {
            $query->where('semester', $request->semester);
        }

        if ($request->school_year) {
            $query->where('school_year', $request->school_year);
        }

        return DataTables::of($query)
            ->addColumn('student_no', function ($grade) {
                return $grade->student->student_number ?? '—';
            })
            ->addColumn('student_name', function ($grade) {
                return $grade->student->user->name ?? '—';
            })
            ->addColumn('program_code', function ($grade) {
                return $grade->student->program->code ?? '—';
            })
            ->addColumn('status', function ($grade) {
                if ($grade->grade === 'INC' || $grade->grade === 'DRP') {
                    return '<span class="badge bg-label-warning">' . $grade->grade . '</span>';
                }
                $numGrade = floatval($grade->grade);
                return ($numGrade >= 1.0 && $numGrade <= 3.0)
                    ? '<span class="badge bg-label-success">Passed</span>'
                    : '<span class="badge bg-label-danger">Failed</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    // ==========================================
    // Grade Import History
    // ==========================================

    public function gradeImportHistory()
    {
        return view('app.admin_panel.reports.grade_import_history');
    }

    public function gradeImportHistoryData(Request $request)
    {
        $query = GradeImport::with(['academic_period', 'user']);

        return DataTables::of($query)
            ->addColumn('uploader', function ($import) {
                return $import->user->name ?? '—';
            })
            ->addColumn('period', function ($import) {
                return $import->academic_period->name ?? '—';
            })
            ->addColumn('stats', function ($import) {
                return "{$import->valid_rows} Valid / {$import->invalid_rows} Invalid / {$import->total_rows} Total";
            })
            ->addColumn('status_badge', function ($import) {
                if ($import->status === 'committed') {
                    return '<span class="badge bg-label-success">Committed</span>';
                } else if ($import->status === 'failed') {
                    return '<span class="badge bg-label-danger">Failed</span>';
                }
                return '<span class="badge bg-label-warning">' . ucfirst($import->status) . '</span>';
            })
            ->editColumn('created_at', function ($import) {
                return $import->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }
}
