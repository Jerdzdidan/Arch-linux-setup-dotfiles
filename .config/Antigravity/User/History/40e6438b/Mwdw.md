# Email Announcement System

## Tasks

### Database & Model
- [x] Create `announcements` migration
- [x] Create `Announcement` model with casts and relationships

### Mail & Queue
- [x] Create `AnnouncementMail` Mailable
- [x] Create `SendAnnouncementEmail` Job
- [x] Create email Blade template

### Controller & Auth
- [x] Create `AnnouncementController` (shared by admin/officer)
- [x] Add `is-officer` Gate to `AppServiceProvider`

### Routes
- [x] Add admin announcement routes
- [x] Add officer announcement routes

### Views
- [x] Create admin announcements index view (compose + history)
- [x] Create officer announcements index view (compose + history)
- [x] Add sidebar items for both panels
- [x] Rewrite views to match existing design standards (no custom CSS, use components)

### Verification
- [ ] Run migration
- [ ] Test admin compose + send flow
- [ ] Test officer compose + send flow
