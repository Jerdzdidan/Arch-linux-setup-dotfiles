# Homepage Announcement Feature

Build a two-pane homepage with admin-created announcements (main pane) and a sidebar (user info + quick links). The existing email announcement system stays separate and untouched — this is a new, display-only feature for the homepage.

## User Review Required

> [!IMPORTANT]
> **Separate from Email Announcements:** This plan creates a brand-new `homepage_announcements` table and model, leaving all existing email announcement code (`AnnouncementController`, `Announcement` model, email views) untouched. The sidebar label will be renamed to "Email Announcements" for clarity. Let me know if you'd prefer to merge these systems instead.

> [!IMPORTANT]
> **Admin-only creation:** Only admins can create/delete homepage announcements. Officers and students can only view them on the homepage. Confirm if officers should also have create permissions.

---

## Proposed Changes

### Database Layer

#### [NEW] [create_homepage_announcements_table.php](file:///opt/lampp/htdocs/AU-AIS/database/migrations/2026_03_04_000000_create_homepage_announcements_table.php)

New migration for `homepage_announcements` table:
- `id` — primary key
- `title` — string, required
- `body` — text (rich HTML content)
- `is_pinned` — boolean, default false (pinned announcements always show at the top)
- `posted_by` — foreign key to `users`
- `timestamps`

---

### Model Layer

#### [NEW] [HomepageAnnouncement.php](file:///opt/lampp/htdocs/AU-AIS/app/Models/HomepageAnnouncement.php)

New Eloquent model with:
- `fillable`: `title`, `body`, `is_pinned`, `posted_by`
- `casts`: `is_pinned` → boolean
- Relationship: `poster()` — belongsTo `User`

---

### Controller Layer

#### [NEW] [HomepageAnnouncementController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/HomepageAnnouncementController.php)

New controller with:
- `index()` — Returns `home.blade.php` with paginated announcements, user info, and quick links data
- `store(Request $request)` — Admin-only. Validates and creates a homepage announcement (AJAX)
- `destroy($id)` — Admin-only. Deletes a homepage announcement (AJAX)

---

### Routes

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php)

- **Replace** the home route closure with `HomepageAnnouncementController@index`
- **Add** admin routes for `homepage-announcements/store` and `homepage-announcements/destroy/{id}`

```diff
-Route::get('/', function () {
-    return view('home');
-})->middleware('auth')->name('home');
+Route::get('/', [HomepageAnnouncementController::class, 'index'])
+    ->middleware('auth')->name('home');

 // Inside admin prefix group:
+Route::post('homepage-announcements/store', [..., 'store'])->name('homepage-announcements.store');
+Route::delete('homepage-announcements/{id}', [..., 'destroy'])->name('homepage-announcements.destroy');
```

---

### View Layer

#### [MODIFY] [home.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/home.blade.php)

Complete rewrite with two-pane layout:

**Main Content Pane (col-lg-8):**
- Admin-only "Post Announcement" button (opens offcanvas/modal with Quill editor)
- Announcements feed — cards displayed in reverse chronological order, pinned first
- Each card shows: title, body (HTML rendered), posted by, timestamp
- Admin sees a delete button on each card
- Empty state message when no announcements exist
- Pagination (Laravel server-side)

**Sidebar Pane (col-lg-4):**
- **User Info Box** — Card showing authenticated user name, user type, and email
- **Quick Links Box** — Context-aware links based on user type:
  - Admin: Academic Periods, Students, Grade Imports, Email Announcements 
  - Officer: Student Progress, Email Announcements
  - Student: Academic Progress, Grades, Manual

---

### CSS

#### [NEW] [home.css](file:///opt/lampp/htdocs/AU-AIS/public/css/app/home.css)

Minimal CSS for:
- Announcement card styling (consistent with Sneat card patterns)
- Sidebar card styling
- Responsive stacking on mobile (sidebar below main)

---

### Sidebar Renaming

#### [MODIFY] [admin.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/admin.blade.php)
#### [MODIFY] [officer.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/sidebar/officer.blade.php)

Rename sidebar "Announcements" to "Email Announcements" to differentiate from homepage announcements.

---

## Verification Plan

### Browser Verification

Verify visually with the browser tool by:

1. **Log in as Admin** — Confirm homepage shows two-pane layout with user info, quick links, and a "Post Announcement" button. Create a test announcement and verify it appears in the feed.
2. **Log in as Student** — Confirm homepage shows announcements (read-only), user info, and student-specific quick links. No create/delete buttons visible.
3. **Log in as Officer** — Same as student but with officer-specific quick links.

### Manual Verification

After implementation, the user can verify by:
1. Logging in as admin at `/` and posting an announcement
2. Logging in as other user types and confirming the announcement is visible without create/delete controls
3. Checking that the "Email Announcements" sidebar item still correctly navigates to the email announcement page
