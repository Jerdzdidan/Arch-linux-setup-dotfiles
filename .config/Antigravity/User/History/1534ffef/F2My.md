# Direct Student Import — Implementation Plan

**New approach**: Add an "Import Students" button directly to the Student Accounts page. Upload CSV → valid rows create `Student` records immediately → invalid rows are returned and displayed in a results modal with error reasons.

## Flow

```
Upload CSV → Parse rows → For each row:
  ├─ Valid? → Create Student + fire events → ✅ imported
  └─ Invalid? → Collect errors → ❌ shown in results modal
```

## Cleanup — Files to Delete

| File | Reason |
|---|---|
| `StudentImportController.php` | Old staging controller, no longer needed |
| `StudentImportRowController.php` | Old staging row controller |
| `StudentImport.php` model | Old staging model |
| `StudentImportRow.php` model | Old staging model |
| `StudentImportRowValidator.php` | Validation moves into `StudentsImport.php` |
| `student_imports` migration | No staging tables needed |
| `student_import_rows` migration | No staging tables needed |
| `student_import_management/` views | Empty dirs from previous attempt |
| Student import routes in `web.php` | Routes added in previous attempt |

---

## Proposed Changes

### Import Logic

#### [MODIFY] [StudentsImport.php](file:///opt/lampp/htdocs/AU-AIS/app/Imports/StudentsImport.php)
- Rename class to `StudentsImport`, remove `StudentImportRow`/`StudentImport` references
- No constructor (no staging ID needed)
- For each row: validate → if valid, create `Student` + fire `StudentCreationEvent` and `StudentAcademicProgressCreate` → if invalid, collect `{row_number, student_number, name, program_code, errors[]}`
- Expose `getImportedCount()`, `getFailedCount()`, `getFailedRows()` for the controller to read results

### Controller

#### [MODIFY] [StudentUserController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/AdminPanel/StudentUserController.php)
- Add `importStudents(Request $request)` method:
  - Validates file upload
  - Calls `Excel::import()` with the new `StudentsImport`
  - Returns JSON with `imported_count`, `failed_count`, `failed_rows[]`

### Route

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php)
- Add: `Route::post('students/import', [StudentUserController::class, 'importStudents'])->name('students.import');`
- Remove old `students/import` route group from previous attempt

### View

#### [MODIFY] [index.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/user_management/student_accounts/index.blade.php)
- Add "Import Students" button next to "Add New Account"
- Add import file modal (offcanvas with file input)
- Add results modal showing: success count, failed count, and a table of failed rows with columns: Row #, Student No., Name, Program Code, Error(s)

---

## Verification Plan

### Automated
- `php -l` on all modified PHP files
- `php artisan route:list --name=students.import`

### Manual
- Upload a CSV → valid rows should appear in the students DataTable, invalid rows should display in the results modal with reasons
