<?php

namespace App\Http\Controllers;

use App\Models\HomepageAnnouncement;
use Illuminate\Http\Request;

class HomepageAnnouncementController extends Controller
{
    /**
     * Display the homepage with announcements.
     */
    public function index()
    {
        $announcements = HomepageAnnouncement::with('poster')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('home', compact('announcements'));
    }

    /**
     * Store a new homepage announcement (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        HomepageAnnouncement::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_pinned' => $validated['is_pinned'] ?? false,
            'posted_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement posted successfully.',
        ]);
    }

    /**
     * Delete a homepage announcement (Admin only).
     */
    public function destroy($id)
    {
        $announcement = HomepageAnnouncement::findOrFail($id);
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully.',
        ]);
    }

    /**
     * Toggle pin status of a homepage announcement (Admin only).
     */
    public function togglePin($id)
    {
        $announcement = HomepageAnnouncement::findOrFail($id);
        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        return response()->json([
            'success' => true,
            'message' => $announcement->is_pinned ? 'Announcement pinned.' : 'Announcement unpinned.',
        ]);
    }
}
