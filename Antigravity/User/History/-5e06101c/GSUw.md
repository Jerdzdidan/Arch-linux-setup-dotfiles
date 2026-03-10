# Restrict Officer Announcements to Their Department

- [/] Modify `AnnouncementController.php`
  - [/] `getFilters()` — filter programs by officer's department
  - [ ] `store()` — validate program belongs to officer's department, inject department_id into filters
  - [ ] `getRecipientCount()` — inject department_id into temp announcement filters
- [ ] Modify `Announcement.php`
  - [ ] `buildRecipientsQuery()` — add department_id filter to scope students
- [ ] Manual verification
