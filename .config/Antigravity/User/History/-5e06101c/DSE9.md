# Restrict Officer Announcements to Their Department

- [x] Modify `AnnouncementController.php`
  - [x] `getFilters()` — filter programs by officer's department
  - [x] `store()` — validate program belongs to officer's department, inject department_id into filters
  - [x] `getRecipientCount()` — inject department_id into temp announcement filters
- [x] Modify `Announcement.php`
  - [x] `buildRecipientsQuery()` — add department_id filter to scope students
- [x] Manual verification
