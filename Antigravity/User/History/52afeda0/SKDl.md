# Student Import Module — Task Checklist

## Research
- [x] Study Grade Import architecture (controllers, models, services, imports, migrations, views, routes)
- [x] Inventory existing Student Import stubs

## Implementation

### Backend
- [ ] Fix `StudentsImport.php` — rename class from `GradesImport` to `StudentsImport`
- [ ] Fix `StudentImportController.php` — fix import class bug, remove `index()` commented code, fix `update()` unique rule
- [ ] Rewrite `StudentImportRowController.php` — adapt from grade to student domain
- [ ] Fill in `StudentImport` model — fillable, relationships
- [ ] Fill in `StudentImportRow` model — fillable, casts, relationships
- [ ] Fix `StudentImportRowValidator.php` — minor cleanup

### Views
- [ ] Create `student_import_management/index.blade.php`
- [ ] Create `student_import_management/create_form.blade.php`
- [ ] Create `student_import_management/update_form.blade.php`
- [ ] Create `student_import_management/student_import_rows/index.blade.php`
- [ ] Create `student_import_management/student_import_rows/form.blade.php`
- [ ] Create `student_import_management/student_import_rows/import_form.blade.php`
- [ ] Create `student_import_management/student_import_rows/modal.blade.php`

### Routes
- [ ] Add student import routes to `web.php`

## Verification
- [ ] Run migration check
- [ ] Verify no PHP syntax errors
