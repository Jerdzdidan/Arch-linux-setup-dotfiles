<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeImport;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // ─────────────────────────────────────────────
    //  ADMIN DASHBOARD
    // ─────────────────────────────────────────────

    public function adminDashboard()
    {
        return view('app.admin_panel.dashboard.index');
    }

    public function adminStats()
    {
        // ── Summary Cards ──
        $totalStudents  = Student::count();
        $totalOfficers  = User::where('user_type', 'OFFICER')->count();
        $totalPrograms  = Program::count();
        $totalDepartments = Department::count();

        // ── Current Academic Period ──
        $currentPeriod = AcademicPeriod::where('is_current', true)->first();

        // ── Students by Program ──
        $studentsByProgram = Program::withCount('students')
            ->orderBy('students_count', 'desc')
            ->get()
            ->map(fn($p) => ['label' => $p->code, 'count' => $p->students_count]);

        // ── Students by Year Level ──
        $studentsByYear = Student::selectRaw('year_level, COUNT(*) as count')
            ->groupBy('year_level')
            ->orderBy('year_level')
            ->get()
            ->map(fn($s) => ['label' => 'Year ' . $s->year_level, 'count' => $s->count]);

        // ── Grade Distribution ──
        $gradeDistribution = $this->buildGradeDistribution();

        // ── Recent Grade Imports ──
        $recentImports = GradeImport::with('academic_period')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($gi) => [
                'id'              => $gi->id,
                'filename'        => $gi->filename,
                'academic_period' => $gi->academic_period->name ?? '—',
                'total_rows'      => $gi->total_rows,
                'valid_rows'      => $gi->valid_rows,
                'invalid_rows'    => $gi->invalid_rows,
                'status'          => $gi->status,
                'date'            => $gi->created_at->format('M d, Y'),
            ]);

        return response()->json([
            'totalStudents'      => $totalStudents,
            'totalOfficers'      => $totalOfficers,
            'totalPrograms'      => $totalPrograms,
            'totalDepartments'   => $totalDepartments,
            'currentPeriod'      => $currentPeriod ? $currentPeriod->name : 'Not Set',
            'studentsByProgram'  => $studentsByProgram,
            'studentsByYear'     => $studentsByYear,
            'gradeDistribution'  => $gradeDistribution,
            'recentImports'      => $recentImports,
        ]);
    }

    // ─────────────────────────────────────────────
    //  OFFICER DASHBOARD
    // ─────────────────────────────────────────────

    public function officerDashboard()
    {
        return view('app.officer_panel.dashboard.index');
    }

    public function officerStats()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        // Programs in the officer's department
        $departmentPrograms = Program::where('department_id', $departmentId)->get();
        $programIds = $departmentPrograms->pluck('id');

        // ── Summary Cards ──
        $totalStudents = Student::whereIn('program_id', $programIds)->count();
        $totalPrograms = $departmentPrograms->count();
        $department    = Department::find($departmentId);

        // ── Current Academic Period ──
        $currentPeriod = AcademicPeriod::where('is_current', true)->first();

        // ── Students by Program (department-scoped) ──
        $studentsByProgram = $departmentPrograms->map(function ($p) {
            return [
                'label' => $p->code,
                'count' => Student::where('program_id', $p->id)->count(),
            ];
        });

        // ── Students by Year Level (department-scoped) ──
        $studentsByYear = Student::whereIn('program_id', $programIds)
            ->selectRaw('year_level, COUNT(*) as count')
            ->groupBy('year_level')
            ->orderBy('year_level')
            ->get()
            ->map(fn($s) => ['label' => 'Year ' . $s->year_level, 'count' => $s->count]);

        // ── Grade Distribution (department-scoped) ──
        $studentIds = Student::whereIn('program_id', $programIds)->pluck('id');
        $gradeDistribution = $this->buildGradeDistribution($studentIds);

        return response()->json([
            'departmentName'     => $department->name ?? '—',
            'totalStudents'      => $totalStudents,
            'totalPrograms'      => $totalPrograms,
            'currentPeriod'      => $currentPeriod ? $currentPeriod->name : 'Not Set',
            'studentsByProgram'  => $studentsByProgram,
            'studentsByYear'     => $studentsByYear,
            'gradeDistribution'  => $gradeDistribution,
        ]);
    }

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────

    /**
     * Build grade distribution buckets.
     * Optionally scoped to a set of student IDs.
     */
    private function buildGradeDistribution($studentIds = null)
    {
        $buckets = [
            '1.00–1.50' => [1.00, 1.50],
            '1.75–2.00' => [1.75, 2.00],
            '2.25–2.50' => [2.25, 2.50],
            '2.75–3.00' => [2.75, 3.00],
            '5.00'      => [5.00, 5.00],
        ];

        $distribution = [];

        foreach ($buckets as $label => [$min, $max]) {
            $query = Grade::whereBetween('grade', [$min, $max]);
            if ($studentIds !== null) {
                $query->whereIn('student_id', $studentIds);
            }
            $distribution[] = [
                'label' => $label,
                'count' => $query->count(),
            ];
        }

        return $distribution;
    }
}
