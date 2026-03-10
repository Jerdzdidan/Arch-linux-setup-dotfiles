# Email Announcement System

## Tasks

### Database & Model
- [ ] Create `announcements` migration
- [ ] Create `Announcement` model with casts and relationships

### Mail & Queue
- [ ] Create `AnnouncementMail` Mailable
- [ ] Create `SendAnnouncementEmail` Job
- [ ] Create email Blade template

### Controller & Auth
- [ ] Create `AnnouncementController` (shared by admin/officer)
- [ ] Add `is-officer` Gate to `AppServiceProvider`

### Routes
- [ ] Add admin announcement routes
- [ ] Add officer announcement routes

### Views
- [ ] Create admin announcements index view (compose + history)
- [ ] Create officer announcements index view (compose + history)
- [ ] Add sidebar items for both panels

### Verification
- [ ] Run migration
- [ ] Test admin compose + send flow
- [ ] Test officer compose + send flow
- [ ] Verify email in logs
