# Admin & Officer Dashboards + Admin Reports

Two features: (1) rich dashboards for admin and officer panels, (2) an admin reports module with filterable, exportable data.

## User Review Required

> [!IMPORTANT]
> **Officer dashboard is department-scoped.** Officers only see students/grades/stats for programs under *their* department. This relies on the existing `department_id` on the `users` table.

> [!IMPORTANT]
> **Report types proposed by the system.** Three report types for admin:
> 1. **Student Directory** — filterable by program, year level, status
> 2. **Grade Performance Report** — GWA distribution per program/year, pass/fail rates
> 3. **Grade Import History** — import logs with success/error rates per academic period
>
> Let me know if you want to add/remove any.

---

## Proposed Changes

### Admin Dashboard

#### [NEW] [DashboardController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/DashboardController.php)

Returns AJAX JSON endpoints for dashboard data:

**`adminDashboard()`** — renders the admin dashboard view
**`adminStats()`** — returns JSON with:
| Stat | Source |
|------|--------|
| Total Students | `Student::count()` |
| Total Officers | `User::where('user_type','OFFICER')->count()` |
| Total Programs | `Program::count()` |
| Total Departments | `Department::count()` |
| Active Academic Period | `AcademicPeriod::where('is_current',true)` |
| Recent Grade Imports | Last 5 `GradeImport` records |
| Students per Program | `Student::groupBy('program_id')` |
| Students per Year Level | `Student::groupBy('year_level')` |
| Grade Distribution | Grades bucketed: 1.0–1.5, 1.75–2.0, 2.25–2.5, 2.75–3.0, 5.0 |

#### [NEW] [admin/dashboard.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/dashboard/index.blade.php)

- **Stats cards row** — Total Students, Officers, Programs, Departments
- **Current Academic Period** info card
- **Charts (Chart.js)** — Students by Program (bar), Students by Year Level (doughnut), Grade Distribution (bar)
- **Recent Grade Imports** — small table showing last 5 imports

---

### Officer Dashboard

**`officerDashboard()`** — renders the officer dashboard view
**`officerStats()`** — returns JSON with same structure as admin, but filtered:
- Students limited to programs under the officer's `department_id`
- Grades limited to those students
- No department/program/officer counts (irrelevant)

| Stat | Source |
|------|--------|
| Department Students | Students in programs under officer's department |
| Department Programs | `Program::where('department_id', ...)` |
| Students per Program | Filtered to department |
| Students per Year Level | Filtered to department |
| Grade Distribution | Filtered to department students |

#### [NEW] [officer/dashboard.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/officer_panel/dashboard/index.blade.php)

Same layout as admin but with department-scoped data and fewer top-level stats cards.

---

### Admin Reports Module

#### [NEW] [ReportController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/AdminPanel/ReportController.php)

Three report endpoints:

1. **`studentDirectory()`** — Returns view with DataTable of all students (filterable by program, year level)
2. **`gradePerformance()`** — GWA stats per program, pass/fail counts, average grades
3. **`gradeImportHistory()`** — Import log with row counts, statuses, per academic period

Each returns both HTML view (GET) and DataTable JSON (GET `/data`).

#### [NEW] [reports/student-directory.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/reports/student_directory.blade.php)

DataTable with columns: Student No, Name, Program, Year Level, Email, Status. Filters: Program (Select2), Year Level. Export button for PDF.

#### [NEW] [reports/grade-performance.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/reports/grade_performance.blade.php)

Stats cards (overall GWA, pass rate, total graded entries) + DataTable grouped by program showing avg GWA, pass/fail counts. Filter by academic period.

#### [NEW] [reports/grade-import-history.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/reports/grade_import_history.blade.php)

DataTable of all grade imports: filename, academic period, total/valid/invalid rows, status, date. Filter by academic period.

---

### Routes

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php)

```
// Admin Dashboard
Route::get('dashboard', [DashboardController, 'adminDashboard'])->name('admin.dashboard');
Route::get('dashboard/stats', [DashboardController, 'adminStats'])->name('admin.dashboard.stats');

// Admin Reports
Route::prefix('reports')->group(function () {
    Route::get('student-directory', ...)->name('admin.reports.students');
    Route::get('student-directory/data', ...)->name('admin.reports.students.data');
    Route::get('grade-performance', ...)->name('admin.reports.grades');
    Route::get('grade-performance/data', ...)->name('admin.reports.grades.data');
    Route::get('grade-import-history', ...)->name('admin.reports.imports');
    Route::get('grade-import-history/data', ...)->name('admin.reports.imports.data');
});

// Officer Dashboard
Route::get('dashboard', [DashboardController, 'officerDashboard'])->name('officer.dashboard');
Route::get('dashboard/stats', [DashboardController, 'officerStats'])->name('officer.dashboard.stats');
```

---

### Sidebar Updates

#### [MODIFY] [admin.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/admin.blade.php)

- Change existing "Dashboard" link from `#` to `admin.dashboard`
- Add "Reports" section header and items: Student Directory, Grade Performance, Grade Import History

#### [MODIFY] [officer.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/officer.blade.php)

- Change existing "Dashboard" link from `#` to `officer.dashboard`

---

### CSS

#### [NEW] [dashboard.css](file:///opt/lampp/htdocs/AU-AIS/public/css/app/dashboard.css)

Chart containers, stat cards, recent imports table styling — consistent with Sneat theme.

---

### Chart.js

Added via CDN in dashboard views. No npm dependency needed.

---

## Verification Plan

### Browser Verification
1. Admin dashboard — all stats cards load, charts render, recent imports table populated
2. Officer dashboard — only department-scoped data appears
3. Admin reports — each report loads with DataTable, filters work, export generates PDF

### Manual
- User can verify by logging in as admin and officer, confirming data accuracy matches database contents
