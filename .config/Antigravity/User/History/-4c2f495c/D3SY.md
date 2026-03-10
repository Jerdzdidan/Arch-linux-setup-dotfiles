# Email Announcement System

Build a module that lets admins and officers compose and send email announcements to users. Admins can target all users (students, officers, admins). Officers can only target students. Emails are sent asynchronously via Laravel's database queue.

## User Review Required

> [!IMPORTANT]
> **SMTP Configuration**: Your `.env` currently has `MAIL_MAILER=log` (emails go to `storage/logs`). You'll need to update this to `smtp` with real credentials (e.g., Gmail, Mailtrap) for actual delivery. The system will work in `log` mode for testing.

> [!IMPORTANT]
> **Queue Worker**: After implementation, you'll need to run `php artisan queue:work` in a terminal to process queued emails. Without it, emails sit in the `jobs` table.

> [!WARNING]
> **No `is-officer` Gate exists** in `AppServiceProvider.php`. I'll add one to protect officer routes, matching the existing `is-admin` / `is-student` pattern.

## Proposed Changes

### Database

#### [NEW] Migration: `create_announcements_table`

```
announcements
├── id
├── subject (string)
├── body (text) — rich HTML content
├── recipient_type (enum: 'all', 'students', 'officers', 'admins')
├── filters (json, nullable) — e.g. {"program_id": 3, "year_level": 4}
├── sent_by (foreignId → users)
├── recipients_count (integer)
├── timestamps
```

---

### Model

#### [NEW] `Announcement.php`

- Belongs to `User` (via `sent_by`)
- `filters` cast to `array`
- Scope method to build recipient query based on `recipient_type` + `filters`

---

### Mail & Queue

#### [NEW] `AnnouncementMail.php` (Mailable)

- Accepts an `Announcement` model
- Uses a branded Blade email template with subject/body
- Implements `ShouldQueue` for async delivery

#### [NEW] `SendAnnouncementEmail.php` (Job)

- Dispatched once per announcement
- Queries recipients based on `recipient_type` + `filters`
- Chunks through recipients and sends `AnnouncementMail` to each
- Skips users with no email

#### [NEW] `announcement.blade.php` (email template)

- Clean HTML email layout with Arellano University branding
- Renders `$announcement->subject` and `$announcement->body`

---

### Controller

#### [NEW] `AnnouncementController.php`

Shared controller for both admin and officer panels:

| Method | Purpose |
|--------|---------|
| `index()` | Show announcements page |
| `getData()` | DataTable AJAX source (filter by sender for officers) |
| `store()` | Validate, create announcement, dispatch job |
| `getRecipientCount()` | AJAX endpoint to preview how many recipients match filters |
| `getFilters()` | Return programs/year-levels for Select2 dropdowns |

**Role-based logic**: Officers automatically get `recipient_type = 'students'` and can't change it. Their recipient query is scoped to students only.

---

### Authorization

#### [MODIFY] [AppServiceProvider.php](file:///opt/lampp/htdocs/AU-AIS/app/Providers/AppServiceProvider.php)

Add `is-officer` Gate:
```php
Gate::define('is-officer', function (User $user) {
    return $user->user_type === 'OFFICER';
});
```

---

### Routes

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php)

**Inside admin prefix** (existing):
```
GET  admin/announcements           → index
GET  admin/announcements/data      → getData
POST admin/announcements/store     → store
GET  admin/announcements/count     → getRecipientCount
GET  admin/announcements/filters   → getFilters
```

**Inside officer prefix** (existing):
```
GET  officer/announcements           → index
GET  officer/announcements/data      → getData
POST officer/announcements/store     → store
GET  officer/announcements/count     → getRecipientCount
GET  officer/announcements/filters   → getFilters
```

---

### Views

#### [NEW] `admin_panel/announcements/index.blade.php`

- **Compose section**: Subject input, rich text body (using a simple contenteditable div or Quill), recipient type dropdown (`All Users`, `Students Only`, `Officers Only`, `Admins Only`), optional filters (program, year level via Select2), recipient count preview, Send button
- **History section**: DataTable listing past announcements (subject, recipient type, count, sent by, date)

#### [NEW] `officer_panel/announcements/index.blade.php`

- Same layout as admin but with recipient type locked to "Students" and no admin/officer targeting options

#### [MODIFY] [admin sidebar](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/admin.blade.php)

Add "Announcements" item under a new "Communications" section.

#### [MODIFY] [officer sidebar](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/officer.blade.php)

Add "Announcements" item under a new "Communications" section.

---

## Verification Plan

### Automated Tests
- `php artisan migrate` — verify announcement migration applies
- `php artisan queue:work --once` — verify a dispatched job processes

### Manual Verification
1. **Admin**: Compose announcement to "All Students", verify recipient count, send, check `announcements` table and `jobs` table
2. **Officer**: Verify recipient type is locked to students, send announcement
3. **Log mode test**: Check `storage/logs/laravel.log` for rendered email output (while `MAIL_MAILER=log`)
4. **History table**: Verify past announcements appear with correct metadata
