<?php

namespace App\Http\Controllers;

use App\Jobs\SendAnnouncementEmail;
use App\Models\Announcement;
use App\Models\Program;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Show the announcements page.
     */
    public function index()
    {
        $userType = auth()->user()->user_type;

        if ($userType === 'ADMIN') {
            return view('app.admin_panel.announcements.index');
        }

        return view('app.officer_panel.announcements.index');
    }

    /**
     * Get announcements data for DataTable.
     */
    public function getData(Request $request)
    {
        $user = auth()->user();

        $query = Announcement::with('sender')
            ->orderBy('created_at', 'desc');

        // Officers only see their own announcements
        if ($user->user_type === 'OFFICER') {
            $query->where('sent_by', $user->id);
        }

        return datatables()->eloquent($query)
            ->addColumn('sender_name', function ($announcement) {
                return $announcement->sender->name ?? 'Unknown';
            })
            ->addColumn('formatted_date', function ($announcement) {
                return $announcement->created_at->format('M d, Y h:i A');
            })
            ->addColumn('recipient_label', function ($announcement) {
                $labels = [
                    'all' => 'All Users',
                    'students' => 'Students',
                    'officers' => 'Officers',
                    'admins' => 'Admins',
                ];
                $label = $labels[$announcement->recipient_type] ?? $announcement->recipient_type;

                // Add filter details if present
                $filters = $announcement->filters ?? [];
                $details = [];
                if (!empty($filters['program_id'])) {
                    $program = Program::find($filters['program_id']);
                    if ($program) $details[] = $program->code;
                }
                if (!empty($filters['year_level'])) {
                    $details[] = "Year {$filters['year_level']}";
                }

                return $label . (!empty($details) ? ' (' . implode(', ', $details) . ')' : '');
            })
            ->rawColumns([])
            ->make(true);
    }

    /**
     * Store a new announcement and dispatch the email job.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];

        // Admins can choose recipient type; officers are locked to students
        if ($user->user_type === 'ADMIN') {
            $rules['recipient_type'] = ['required', 'in:all,students,officers,admins'];
        }

        // Officers: ensure selected program belongs to their department
        if ($user->user_type === 'OFFICER' && $user->department_id) {
            $rules['program_id'] = ['nullable', 'exists:programs,id,department_id,' . $user->department_id];
        } else {
            $rules['program_id'] = ['nullable', 'exists:programs,id'];
        }
        $rules['year_level'] = ['nullable', 'integer', 'min:1', 'max:5'];

        $validated = $request->validate($rules);

        // Build filters
        $filters = [];
        if (!empty($validated['program_id'])) {
            $filters['program_id'] = $validated['program_id'];
        }
        if (!empty($validated['year_level'])) {
            $filters['year_level'] = $validated['year_level'];
        }

        // Officers: always scope to their department
        if ($user->user_type === 'OFFICER' && $user->department_id) {
            $filters['department_id'] = $user->department_id;
        }

        // Determine recipient type
        $recipientType = $user->user_type === 'ADMIN'
            ? $validated['recipient_type']
            : 'students';

        // Create the announcement
        $announcement = Announcement::create([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'recipient_type' => $recipientType,
            'filters' => !empty($filters) ? $filters : null,
            'sent_by' => $user->id,
            'recipients_count' => 0,
        ]);

        // Get the estimated recipient count
        $estimatedCount = $announcement->buildRecipientsQuery()->count();

        if ($estimatedCount === 0) {
            $announcement->delete();
            return response()->json([
                'success' => false,
                'message' => 'No recipients found with the selected filters. Announcement was not sent.',
            ], 422);
        }

        // Update estimated count before dispatching
        $announcement->update(['recipients_count' => $estimatedCount]);

        // Dispatch the email job
        SendAnnouncementEmail::dispatch($announcement);

        return response()->json([
            'success' => true,
            'message' => "Announcement queued for delivery to {$estimatedCount} recipient(s).",
        ]);
    }

    /**
     * Get the estimated recipient count based on filters (AJAX preview).
     */
    public function getRecipientCount(Request $request)
    {
        $user = auth()->user();

        $recipientType = $user->user_type === 'ADMIN'
            ? ($request->get('recipient_type', 'all'))
            : 'students';

        // Build a temporary announcement to use the query builder
        $temp = new Announcement([
            'recipient_type' => $recipientType,
            'filters' => array_filter([
                'program_id' => $request->get('program_id'),
                'year_level' => $request->get('year_level'),
                'department_id' => ($user->user_type === 'OFFICER' && $user->department_id)
                    ? $user->department_id
                    : null,
            ]),
        ]);

        $count = $temp->buildRecipientsQuery()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get filter options (programs, year levels) for Select2 dropdowns.
     */
    public function getFilters()
    {
        $user = auth()->user();

        $programQuery = Program::select('id', 'code', 'name');

        // Officers only see programs in their department
        if ($user->user_type === 'OFFICER' && $user->department_id) {
            $programQuery->where('department_id', $user->department_id);
        }

        $programs = $programQuery
            ->orderBy('code')
            ->get()
            ->map(function ($program) {
                return [
                    'id' => $program->id,
                    'text' => "{$program->code} - {$program->name}",
                ];
            });

        $yearLevels = collect([1, 2, 3, 4, 5])->map(function ($level) {
            return [
                'id' => $level,
                'text' => "Year {$level}",
            ];
        });

        return response()->json([
            'programs' => $programs,
            'year_levels' => $yearLevels,
        ]);
    }
}
