# PhishGuard Codebase Cleanup & Improvement Plan

A comprehensive refactoring of the PhishGuard Laravel codebase to fix bugs, eliminate code duplication, standardize patterns, and improve code quality. All changes are behavior-preserving (except bug fixes) — no new features, just making the existing code correct and clean.

## User Review Required

> [!CAUTION]
> **Bug in `UserController::store()`**: Line 66 overrides `user_type` to `'ADMIN'` regardless of the validated form input. Every user created through this form becomes an admin. The fix will use the validated `user_type` value instead.

> [!WARNING]
> **`GoogleAuthController` is broken**: It uses `name` (single field) instead of `first_name`/`last_name` (which are the actual User model columns), and redirects to a non-existent `dashboard` route. Since `google_id` is not in User's `$fillable`, the update call also silently fails. The fix will split names and redirect to the correct user home route.

> [!IMPORTANT]
> **The `CourseAppController` appears to be dead code** — it renders `user.home.index` but `UserLessonController::index()` already serves the `user.home` route doing the same thing. I'll leave it in place but add a `@deprecated` annotation. Let me know if you want it removed entirely.

## Proposed Changes

### Critical Bug Fixes

---

#### [MODIFY] [UserController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/Admin/UserController.php)

1. **Fix `store()` — line 66 overrides `user_type` to `'ADMIN'`**: Replace manual field assignment with `User::create()` using the validated data, removing the hardcoded override.
2. **Fix `store()` — no response returned**: Add `return response()->json(['success' => true])`.
3. **Fix `update()` — no response returned**: Add `return response()->json(['success' => true])`.
4. **Fix `update()` — `$user->update()` called incorrectly**: Should be `$user->save()` since fields are set manually (or switch to `$user->update($validated)`).
5. **Fix `toggle()` — error message says "Could not delete"**: Change to "Invalid user ID."

---

#### [MODIFY] [GoogleAuthController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/Auth/GoogleAuthController.php)

1. **Fix `name` field → `first_name`/`last_name`**: Split `$googleUser->getName()` into first and last name.
2. **Fix redirect to non-existent `dashboard` route**: Redirect to `user.home` or `admin.home` based on user type.
3. **Fix redirect to non-existent `login` route in catch block**: Redirect to `auth.sign-in`.
4. **Remove unused `Request` import**.

---

#### [MODIFY] [User.php](file:///opt/lampp/htdocs/PhishGuard/app/Models/User.php)

1. **Add `google_id` to `$fillable`** so Google OAuth can actually save the ID.
2. **Add `status` to `$casts`** as `boolean`.

---

#### [MODIFY] [SimulationAttempt.php](file:///opt/lampp/htdocs/PhishGuard/app/Models/SimulationAttempt.php)

1. **Fix division by zero in `isPassed()`** when `total_scenarios` is 0 — add a guard check consistent with `getPercentage()`.

---

### Code Duplication Cleanup

---

#### [MODIFY] [AnalyticsController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/Admin/AnalyticsController.php)

The `quizAnalytics()` method has the same question-stats collection loop copy-pasted for both the per-lesson and aggregated branches. Same for `simulationAnalytics()` with scenario stats. And `getQuizHeatmapData()` / `getSimulationHeatmapData()` repeat the same patterns again.

1. **Extract `collectQuestionStats(Collection $attempts): array`** — shared helper that iterates attempts, decodes `answers_data`, and tallies per-question totals.
2. **Extract `collectScenarioStats(Collection $attempts): array`** — shared helper that iterates attempts, reads `scenario_results`, and tallies per-scenario totals.
3. **Extract `calculateDifficultyData(array $stats, string $labelKey): array`** — takes raw stats and returns the sorted difficulty/success-rate array.
4. **Extract `collectClickData(Collection $attempts): array`** — shared helper for CTR data.
5. **Refactor `quizAnalytics()`** to use the extracted helpers, removing ~40 duplicated lines.
6. **Refactor `simulationAnalytics()`** to use the extracted helpers, removing ~50 duplicated lines.
7. **Refactor `getQuizHeatmapData()` and `getSimulationHeatmapData()`** to reuse the same helpers.

---

### Inconsistent Pattern Fixes

---

#### [MODIFY] [AuthController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/Auth/AuthController.php)

1. **Use Laravel's `confirmed` validation rule** instead of manual password matching. Change `confirm_password` field to `password_confirmation` and use `'password' => 'required|string|min:6|confirmed'`.

---

#### [MODIFY] [ProgressCard.php](file:///opt/lampp/htdocs/PhishGuard/app/View/Components/Table/ProgressCard.php)

1. **Fix namespace casing**: `namespace App\View\Components\table;` → `namespace App\View\Components\Table;`

---

#### [MODIFY] [Lesson.php](file:///opt/lampp/htdocs/PhishGuard/app/Models/Lesson.php)

1. **Add `has_simulation` to `$casts`** as `boolean`.

---

### Route Cleanup

---

#### [MODIFY] [web.php](file:///opt/lampp/htdocs/PhishGuard/routes/web.php)

1. **Add missing `use` imports** for `AnalyticsController` and `UserProgressController` at the top of the file (currently referenced with inline FQCN).
2. **Remove the placeholder route** `Route::get('/placeholder', ...)`.
3. **Change logout from `GET` to `POST`**: `Route::post('logout', ...)` for CSRF protection.

---

### Dead/Stub Code Cleanup

---

#### [MODIFY] [AnalyticsController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/Admin/AnalyticsController.php)

1. **Clean up `exportOverview()`** — currently opens a CSV and writes only headers with no data. Add a `TODO` comment or implement basic data rows.
2. **Add `TODO` annotations** to the empty `exportQuizAnalytics()` and `exportSimulationAnalytics()` stubs.

---

#### [MODIFY] [CourseAppController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/User/CourseAppController.php)

1. **Add `@deprecated` DocBlock** noting that `UserLessonController::index()` serves the same purpose.

---

### N+1 Query Fix

---

#### [MODIFY] [UserProgressController.php](file:///opt/lampp/htdocs/PhishGuard/app/Http/Controllers/Admin/UserProgressController.php)

1. **Fix N+1 in `getData()`**: The `total_lessons` callback runs `Lesson::where('is_active', true)->count()` once per row. Cache this value before the DataTable callback.
2. **Remove empty `filterColumn` callbacks** that do nothing.

---

### Minor Cleanups (across multiple files)

1. Remove `//` empty comments from constructors and class bodies.
2. Fix inconsistent indentation (tabs vs spaces) in `GoogleAuthController` and `UserProgressController`.

---

## Verification Plan

### Automated Tests

Since the existing test suite only has boilerplate Pest tests, I'll verify using Laravel's built-in syntax checker and route listing:

```bash
# Ensure no PHP syntax errors after refactoring
cd /opt/lampp/htdocs/PhishGuard && php artisan route:list
```

```bash
# Run existing Pest tests to ensure nothing is broken
cd /opt/lampp/htdocs/PhishGuard && php artisan test
```

### Manual Verification

> [!IMPORTANT]
> Since these are refactoring changes (no new features), the primary verification is that the app still starts correctly and routes resolve. Could you confirm after the changes that:
> 1. The admin user management page still loads, and creating a new user respects the selected user type
> 2. The analytics overview/quiz/simulation pages still load without errors
> 3. The sign-up page still works (the password field name will change to `password_confirmation`)

Please **check your sign-up Blade view** — if it uses `confirm_password` as the input field name, it will need to be changed to `password_confirmation` to match Laravel's convention. I'll search for this during implementation and update it if found.
