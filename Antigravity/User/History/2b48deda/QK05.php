<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\SimulationAttempt;
use App\Models\StudentLesson;
use App\Models\User;
use App\Models\UserQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\DataTables;

class UserProgressController extends Controller
{
    /**
     * User Progress List
     */
    public function index()
    {
        return view('admin.user-progress.index');
    }

    /**
     * Get User Progress Data for DataTable
     */
    public function getData(Request $request)
    {
        $users = User::where('user_type', 'USER')
            ->with(['studentLessons'])
            ->select(['id', 'first_name', 'last_name', 'email', 'created_at']);

        // Pre-compute once to avoid N+1 in the DataTable callback
        $totalActiveLessons = Lesson::where('is_active', true)->count();

        return DataTables::of($users)
            ->addColumn('lessons_completed', function ($user) {
                return $user->studentLessons()->whereNotNull('completed_at')->count();
            })
            ->addColumn('total_lessons', function ($user) use ($totalActiveLessons) {
                return $totalActiveLessons;
            })
            ->addColumn('quiz_avg', function ($user) {
                $avg = UserQuizAttempt::where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->avg('score');
                return $avg ? round($avg, 2) . '%' : 'N/A';
            })
            ->addColumn('simulation_avg', function ($user) {
                $attempts = SimulationAttempt::where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->get();

                if ($attempts->isEmpty()) return 'N/A';

                $avgPercentage = $attempts->avg(function ($attempt) {
                    return $attempt->total_scenarios > 0
                        ? ($attempt->score / $attempt->total_scenarios) * 100
                        : 0;
                });

                return round($avgPercentage, 2) . '%';
            })
            ->addColumn('actions', function ($user) {
                $encryptedId = Crypt::encryptString($user->id);
                return '<a href="' . route('admin.user-progress.show', $encryptedId) . '" class="btn btn-sm btn-primary">
                    <i class="ri-eye-line me-1"></i> View Progress
                </a>';
            })
            ->editColumn('id', function ($user) {
                return Crypt::encryptString($user->id);
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at->format('Y-m-d H:i:s');
            })
            ->orderColumn('lessons_completed', function ($query, $order) {
                $query->withCount(['studentLessons as completed_count' => function ($q) {
                    $q->whereNotNull('completed_at');
                }])->orderBy('completed_count', $order);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show Individual User Progress
     */
    public function show($id)
    {
        $userId = Crypt::decryptString($id);
        $user = User::findOrFail($userId);

        // Get all lessons with progress
        $lessons = Lesson::where('is_active', true)
            ->with(['studentLessons' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->get()
            ->map(function ($lesson) use ($userId) {
                $progress = $lesson->studentLessons->first();

                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'difficulty' => $lesson->difficulty,
                    'is_completed' => $progress ? $progress->completed_at !== null : false,
                    'content_viewed' => $progress ? $progress->content_viewed : false,
                    'quiz_passed' => $progress ? $progress->quiz_passed : false,
                    'simulations_completed' => $progress ? $progress->simulations_completed : false,
                    'completed_at' => $progress ? $progress->completed_at : null,
                ];
            });

        // Get quiz attempts
        $quizAttempts = UserQuizAttempt::where('user_id', $userId)
            ->with('quiz.lesson')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get();

        // Get simulation attempts
        $simulationAttempts = SimulationAttempt::where('user_id', $userId)
            ->with('lesson')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get();

        // Calculate time spent per lesson
        $timeSpent = StudentLesson::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->with('lesson')
            ->get()
            ->map(function ($progress) {
                $seconds = $progress->created_at->diffInSeconds($progress->completed_at);
                return [
                    'lesson_id' => $progress->lesson_id,
                    'lesson_title' => $progress->lesson->title,
                    'time_seconds' => $seconds,
                    'time_formatted' => gmdate('H:i:s', $seconds)
                ];
            });

        return view('admin.user-progress.show', compact(
            'user',
            'lessons',
            'quizAttempts',
            'simulationAttempts',
            'timeSpent'
        ));
    }
}
