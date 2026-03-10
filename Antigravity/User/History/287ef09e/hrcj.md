# Walkthrough: Email Announcement System

## Summary

Built a complete email announcement module for both admin and officer panels. Admins can send to all users, students, officers, or admins. Officers can only send to students. Emails are dispatched via Laravel's database queue.

## Files Created

| File | Purpose |
|------|---------|
| [create_announcements_table.php](file:///opt/lampp/htdocs/AU-AIS/database/migrations/2026_03_03_000000_create_announcements_table.php) | Migration for `announcements` table |
| [Announcement.php](file:///opt/lampp/htdocs/AU-AIS/app/Models/Announcement.php) | Model with `buildRecipientsQuery()` for targeted filtering |
| [AnnouncementMail.php](file:///opt/lampp/htdocs/AU-AIS/app/Mail/AnnouncementMail.php) | Mailable class |
| [SendAnnouncementEmail.php](file:///opt/lampp/htdocs/AU-AIS/app/Jobs/SendAnnouncementEmail.php) | Queued job that sends emails to all matching recipients |
| [announcement.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/emails/announcement.blade.php) | Branded HTML email template |
| [AnnouncementController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/AnnouncementController.php) | Shared controller with role-based logic |
| [admin index.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/admin_panel/announcements/index.blade.php) | Admin compose + history view |
| [officer index.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/app/officer_panel/announcements/index.blade.php) | Officer compose + history view |

## Files Modified

| File | Change |
|------|--------|
| [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php) | Added announcement routes + `is-officer` gate on officer group |
| [AppServiceProvider.php](file:///opt/lampp/htdocs/AU-AIS/app/Providers/AppServiceProvider.php) | Added `is-officer` Gate |
| [admin sidebar](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/admin.blade.php) | Added "Announcements" under Communications |
| [officer sidebar](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/officer.blade.php) | Added "Announcements" under Communications |

## Before Testing

1. **Run migration**: `php artisan migrate`
2. **Configure SMTP** in `.env` (or leave as `log` to see emails in `storage/logs/laravel.log`)
3. **Start queue worker**: `php artisan queue:work`
