# Homepage Announcement Feature

## Planning
- [x] Explore existing codebase structure, design patterns, components
- [x] Review existing email announcement system
- [/] Write implementation plan
- [ ] Get user approval on plan

## Execution
- [ ] Refactor announcement system (rename email-specific parts, add homepage announcements table)
- [ ] Create migration for `homepage_announcements` table
- [ ] Create `HomepageAnnouncement` model
- [ ] Create `HomepageAnnouncementController`
- [ ] Add routes for homepage announcement CRUD + home page data
- [ ] Update `home` route to use a controller
- [ ] Build `home.blade.php` with two-pane layout (main + sidebar)
- [ ] Add admin "Create Announcement" functionality (modal or form)
- [ ] Create homepage announcement CSS
- [ ] Rename existing email announcements to "Email Announcements" in sidebar

## Verification
- [ ] Visual verification in browser (all user types)
- [ ] Test admin creating announcements
- [ ] Confirm students/officers see announcements but cannot create
