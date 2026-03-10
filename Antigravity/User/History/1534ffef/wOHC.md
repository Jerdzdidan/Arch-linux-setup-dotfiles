# Student Import Module — Implementation Plan

Build a Student Import module by mirroring the existing Grade Import module's architecture. The student import workflow: upload CSV → parse rows into `student_import_rows` → validate → stage → commit (create actual `students` records).

## Key Differences from Grade Import

| Aspect | Grade Import | Student Import |
|---|---|---|
| Linked to | `academic_period_id` | Nothing (standalone) |
| Row fields | student_number, subject_code, subject_name, unit_type, school_year, semester, faculty, credit_unit, grade | student_number, name, program_code, program_name, year_level |
| Commit creates | `Grade` record | `Student` record |
| Validation | Student must exist, subject must exist, valid grade | Student must NOT exist, program must exist, valid format |

---

## Proposed Changes

### Backend Fixes

#### [MODIFY] [StudentsImport.php](file:///opt/lampp/htdocs/AU-AIS/app/Imports/StudentsImport.php)
- Rename class from `GradesImport` to `StudentsImport`

#### [MODIFY] [StudentImportController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/AdminPanel/StudentImportController.php)
- Add `index()` method returning the student import management view
- Fix `store()` — currently calls `new StudentImport($import->id)` which is the Model, should be `new StudentsImport(...)`
- Fix `update()` — unique rule references `grade_imports` table instead of `student_imports`

#### [MODIFY] [StudentImportRowController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/AdminPanel/StudentImportRowController.php)
- Full rewrite: adapt all references from Grade domain → Student domain
- Change `findGradeImport` → `findStudentImport`, use `StudentImport` model
- Change `findGradeImportRow` → `findStudentImportRow`, use `StudentImportRow` model
- Replace `validateRequest()` to validate student fields (student_number, name, program_code) instead of grade fields
- Replace `prepareRowData()` for student data
- Replace `commitRow()` / `commitAll()` to create `Student` records (with program_id, curriculum_id, year_level) instead of `Grade` records
- Replace `unCommit()` / `uncommitAll()` to delete `Student` records
- `validateAllRows()` uses `Program` instead of `Subject`
- Remove grade-specific helpers (`findSubjectOrFail`, grade display logic)

#### [MODIFY] [StudentImport.php](file:///opt/lampp/htdocs/AU-AIS/app/Models/StudentImport.php)
- Add `$fillable`, `rows()` relationship

#### [MODIFY] [StudentImportRow.php](file:///opt/lampp/htdocs/AU-AIS/app/Models/StudentImportRow.php)
- Add `$fillable`, `$casts`, `studentImport()` relationship, `getErrorMessages()`

#### [MODIFY] [StudentImportRowValidator.php](file:///opt/lampp/htdocs/AU-AIS/app/Services/StudentImportRowValidator.php)
- Fix `$row->subject_name` → `$row->program_name` in `validateProgram()`

---

### Views (all [NEW])

Mirror the grade import views structure:

#### [NEW] [index.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/student_import_management/index.blade.php)
- DataTable with columns: Id, Filename, Valid Rows, Invalid Rows, Total Rows, Status, Processed At, Actions

#### [NEW] [create_form.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/student_import_management/create_form.blade.php)
- File upload only (no academic period — unlike grade import)

#### [NEW] [update_form.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/student_import_management/update_form.blade.php)
- Filename field only

#### [NEW] Student Import Rows views (4 files in `student_import_rows/`)
- `index.blade.php` — DataTable with student_number, name, program_code, program_name, year_level, validity, status, actions
- `form.blade.php` — Add/edit form for student_number, name, program_code
- `import_form.blade.php` — CSV import form
- `modal.blade.php` — Error messages modal

---

### Routes

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php)
- Add student import route group under `admin` prefix, mirroring grade import routes but with `students.import.*` naming

---

## Verification Plan

### Automated
- `php artisan route:list --name=students.import` — verify all routes are registered
- `php -l` on all modified/new PHP files — verify no syntax errors

### Manual Verification
- After implementation, the user can navigate to the Student Import Management page, upload a CSV with `student_number`, `name`, `program_code` columns, review staged rows, fix invalid rows, and commit to create students.
