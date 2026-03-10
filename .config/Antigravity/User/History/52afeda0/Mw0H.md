# Direct Student Import — Task Checklist

## Cleanup (remove old staging approach)
- [ ] Delete `StudentImportController.php`
- [ ] Delete `StudentImportRowController.php`
- [ ] Delete `StudentImport.php` model
- [ ] Delete `StudentImportRow.php` model
- [ ] Delete `StudentImportRowValidator.php` service
- [ ] Delete `student_imports` migration
- [ ] Delete `student_import_rows` migration
- [ ] Delete empty `student_import_management/` view directories
- [ ] Remove student import routes from `web.php`

## Implementation
- [ ] Rewrite `StudentsImport.php` — direct import (no staging)
- [ ] Add `importStudents()` method to `StudentUserController`
- [ ] Add import route to `web.php`
- [ ] Add import button + import modal to student accounts `index.blade.php`
- [ ] Add results modal (shows invalid rows with errors) to `index.blade.php`

## Verification
- [ ] PHP syntax check
- [ ] Route verification
