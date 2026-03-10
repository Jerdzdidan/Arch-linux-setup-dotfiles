# PhishGuard Codebase Cleanup & Improvement

## 1. Fix Critical Bugs
- [x] Fix `UserController::store()` — `user_type` is overridden to `'ADMIN'` on line 66, ignoring the validated input
- [x] Fix `GoogleAuthController` — uses `name` instead of `first_name`/`last_name` and redirects to non-existent `dashboard`
- [x] Add `google_id` to User model `$fillable` array
- [x] Fix `SimulationAttempt::isPassed()` — division by zero when `total_scenarios` is 0

## 2. Eliminate Code Duplication in AnalyticsController
- [ ] Extract `collectQuestionStats()` private helper (used 3 times)
- [ ] Extract `collectScenarioStats()` private helper (used 3 times)
- [ ] Extract reusable date-filtering scope/closure
- [ ] DRY up `quizAnalytics()` and `simulationAnalytics()` methods

## 3. Fix Inconsistent Patterns & Code Smells
- [ ] Fix `UserController::store()` and `update()` — no response returned; use `create()` instead of manual save
- [ ] Fix `UserController::toggle()` error message saying "Could not delete" for a toggle action
- [ ] Fix `ProgressCard` namespace typo (`table` vs `Table`)
- [ ] Remove `//` comment artifacts in `Quiz`, `UserQuizAttempt`, and `UserLessonController`
- [ ] Standardize `$user->update()` vs `$user->save()` usage in `UserController`

## 4. Route Cleanup
- [ ] Use fully-qualified imports in `web.php` for `AnalyticsController` and `UserProgressController` instead of inline FQCN
- [ ] Remove placeholder route `Route::get('/placeholder', ...)`
- [ ] Use `POST` for logout instead of `GET` (security best practice)
- [ ] Add route model binding or middleware for admin role-check

## 5. Improve Auth Flow
- [ ] Use Laravel's `confirmed` validation rule instead of manual password matching in `AuthController::store()`
- [ ] Remove unused `Request` import from `GoogleAuthController`

## 6. Stub / Dead Code Removal
- [ ] Implement or remove stub `exportQuizAnalytics()` and `exportSimulationAnalytics()` in `AnalyticsController`
- [ ] Clean up empty `exportOverview()` that writes no data rows
- [ ] Assess whether `CourseAppController` is dead code (unused, `UserLessonController` serves same purpose)

## 7. N+1 Query Fixes
- [ ] Fix `UserProgressController::getData()` — N+1 on `Lesson::count()` inside DataTable column callback
- [ ] Cache or pre-compute `total_lessons` outside the callback

## 8. Model Improvements
- [ ] Add missing `table` property to `StudentLesson` if table name doesn't match convention
- [ ] Add `status` to User `$casts` as boolean
- [ ] Add `has_simulation` to Lesson `$casts`

## 9. View Component Cleanup
- [ ] Fix `ProgressCard.php` namespace casing (`table` → `Table`)
- [ ] Remove empty `//` constructor comments across view components
