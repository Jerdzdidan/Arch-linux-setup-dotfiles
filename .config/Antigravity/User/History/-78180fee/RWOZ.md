# Homepage Announcement Feature

## Planning
- [x] Explore existing codebase structure, design patterns, components
- [x] Review existing email announcement system
- [/] Write implementation plan
- [ ] Get user approval on plan

## Execution
- [x] Refactor announcement system (rename email-specific parts, add homepage announcements table)
- [x] Create migration for `homepage_announcements` table
- [x] Create `HomepageAnnouncement` model
- [x] Create `HomepageAnnouncementController`
- [x] Add routes for homepage announcement CRUD + home page data
- [x] Update `home` route to use a controller
- [/] Build `home.blade.php` with two-pane layout (main + sidebar)
- [/] Add admin "Create Announcement" functionality (modal or form)
- [/] Create homepage announcement CSS
- [x] Rename existing email announcements to "Email Announcements" in sidebar

## Verification
- [ ] Visual verification in browser (all user types)
- [ ] Test admin creating announcements
- [ ] Confirm students/officers see announcements but cannot create
