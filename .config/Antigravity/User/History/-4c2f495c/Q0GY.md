# Student Email Detection & Prompt Feature

When a student logs in and has no email on record, automatically show a popup modal asking them to provide their email address. The email is then saved to the `users` table via an AJAX call.

## Key Observations

- The `users` table already has a **nullable `email` column** — no migration needed.
- The `Student` model belongs to `User`, so the email lives on the `User` model.
- The existing `StudentInformationCheck` middleware already runs on every student portal request — we'll share the boolean flag from there.
- The base layout already includes Bootstrap, jQuery, SweetAlert2, and Toastr — all available for the modal and feedback.

## Proposed Changes

### Middleware

#### [MODIFY] [StudentInformationCheck.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Middleware/StudentInformationCheck.php)

After the existing year-level logic, add a `View::share('studentHasEmail', ...)` call so that all student views can access the boolean. The value is `true` if `auth()->user()->email` is not null/empty, `false` otherwise.

---

### Route & Controller

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/AU-AIS/routes/web.php)

Add a new `POST` route inside the existing `student` prefix group:

```php
Route::post('update-email', [StudentPortalController::class, 'updateEmail'])->name('student.update_email');
```

#### [NEW] [StudentPortalController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/StudentPortal/StudentPortalController.php)

A small controller with a single `updateEmail` method:
- Validate `email` (required, valid email, unique in `users` table for the current user).
- Update `auth()->user()->email`.
- Return a JSON success response.

---

### Views

#### [MODIFY] [base.blade.php](file:///opt/lampp/htdocs/AU-AIS/resources/views/layout/base.blade.php)

Inside the body (after the layout wrapper, before the Core JS), add a student-specific block gated by `@if(auth()->user()->user_type === 'STUDENT' && !$studentHasEmail)`:
- A Bootstrap modal (`#emailPromptModal`) with a simple form containing an email input and a submit button.
- The modal uses `data-bs-backdrop="static"` and `data-bs-keyboard="false"` so the student cannot dismiss it without entering an email.

In the `@yield('scripts')` area (or a new `@stack('modals')` section), add inline JS that:
- Shows the modal on page load via `new bootstrap.Modal(...)`.
- Handles form submission via `$.ajax` POST to the `student.update_email` route.
- On success, shows a Toastr success message and reloads the page (so the modal won't reappear).
- On validation error, displays error messages inline.

## Verification Plan

### Manual Verification

1. **Log in as a student whose `users.email` is `NULL`** — the modal should appear immediately and be un-dismissable.
2. **Submit an invalid email** (e.g., `notanemail`) — validation error should be shown inline.
3. **Submit a valid, unique email** — Toastr success message appears, page reloads, modal no longer appears.
4. **Log in as a student who already has an email** — no modal should appear.

> [!IMPORTANT]
> Since authentication and session flows are involved, I recommend verifying this in the browser. Would you like me to test this by running the dev server after implementation?
