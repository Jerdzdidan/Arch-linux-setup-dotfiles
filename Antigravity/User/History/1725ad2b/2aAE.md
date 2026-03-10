# Restrict Officer Announcements to Their Department

Officers should only be able to send email announcements to students within their own department. The program dropdown should also only show programs belonging to the officer's department.

## Data Model Summary

- `User` has `department_id` → `Department`
- `Program` has `department_id` → `Department`
- `Student` has `program_id` → `Program`
- `Department` has many `Programs` and many `Users`

So: **Officer → Department → Programs → Students** is the chain we need to enforce.

## Proposed Changes

### AnnouncementController

#### [MODIFY] [AnnouncementController.php](file:///opt/lampp/htdocs/AU-AIS/app/Http/Controllers/AnnouncementController.php)

**1. `getFilters()` method** — When the user is an officer, only return programs that belong to the officer's department:

```diff
 public function getFilters()
 {
-    $programs = Program::select('id', 'code', 'name')
+    $user = auth()->user();
+
+    $programQuery = Program::select('id', 'code', 'name');
+
+    // Officers only see programs in their department
+    if ($user->user_type === 'OFFICER' && $user->department_id) {
+        $programQuery->where('department_id', $user->department_id);
+    }
+
+    $programs = $programQuery
         ->orderBy('code')
         ->get()
```

**2. `store()` method** — When storing an announcement from an officer, automatically scope the filters to enforce the department constraint. Also validate that any selected `program_id` belongs to the officer's department:

```diff
+    // Officers: ensure selected program belongs to their department
+    if ($user->user_type === 'OFFICER' && $user->department_id) {
+        $rules['program_id'] = ['nullable', 'exists:programs,id,department_id,' . $user->department_id];
+    }
```

And store the officer's `department_id` in the filters so `buildRecipientsQuery()` can scope by it:

```diff
+    // Officers: always scope to their department
+    if ($user->user_type === 'OFFICER' && $user->department_id) {
+        $filters['department_id'] = $user->department_id;
+    }
```

**3. `getRecipientCount()` method** — Also inject the officer's `department_id` into the temporary announcement's filters so the preview count is accurate:

```diff
     $temp = new Announcement([
         'recipient_type' => $recipientType,
         'filters' => array_filter([
             'program_id' => $request->get('program_id'),
             'year_level' => $request->get('year_level'),
+            'department_id' => ($user->user_type === 'OFFICER' && $user->department_id)
+                ? $user->department_id
+                : null,
         ]),
     ]);
```

---

### Announcement Model

#### [MODIFY] [Announcement.php](file:///opt/lampp/htdocs/AU-AIS/app/Models/Announcement.php)

**`buildRecipientsQuery()` method** — Add a `department_id` filter that scopes students to those whose program belongs to the given department:

```diff
+    if (!empty($filters['department_id'])) {
+        $departmentId = $filters['department_id'];
+        $query->whereHas('student', function ($q) use ($departmentId) {
+            $q->whereHas('program', function ($pq) use ($departmentId) {
+                $pq->where('department_id', $departmentId);
+            });
+        });
+    }
```

## Verification Plan

### Manual Verification

1. Log in as an **officer** user that has a `department_id` set
2. Navigate to **Email Announcements** page
3. Click **Compose Announcement** — verify the Program dropdown only shows programs from the officer's department
4. Select a program and year level — verify the estimated recipient count only includes students from that department
5. Send an announcement — verify it was stored with the `department_id` filter in the database
6. Log in as **admin** — verify the Program dropdown still shows all programs (no restriction)
