<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\SimulationAttempt;
use App\Models\StudentLesson;
use App\Models\User;
use App\Models\UserQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // ─── Shared Constants ───────────────────────────────────────────────

    private const DIFFICULTY_THRESHOLDS = [
        80 => 'Easy',
        60 => 'Medium',
        40 => 'Hard',
    ];
    private const DIFFICULTY_DEFAULT = 'Very Hard';

    // ─── Public Actions ─────────────────────────────────────────────────

    /**
     * Analytics Overview
     */
    public function overview(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $stats = [
            'total_users'       => User::where('user_type', 'USER')->count(),
            'total_lessons'     => Lesson::where('is_active', true)->count(),
            'total_quizzes'     => UserQuizAttempt::query()
                ->when($startDate, fn($q) => $q->whereDate('completed_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('completed_at', '<=', $endDate))
                ->count(),
            'total_simulations' => SimulationAttempt::whereNotNull('completed_at')
                ->when($startDate, fn($q) => $q->whereDate('completed_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('completed_at', '<=', $endDate))
                ->count(),
        ];

        $lessonCompletionRates = Lesson::withCount([
            'studentLessons as completed_count' => fn($q) => $q->whereNotNull('completed_at'),
            'studentLessons as started_count',
        ])->where('is_active', true)->get()->map(fn($lesson) => [
            'lesson_id'       => $lesson->id,
            'title'           => $lesson->title,
            'completion_rate' => $lesson->started_count > 0
                ? round(($lesson->completed_count / $lesson->started_count) * 100, 2)
                : 0,
            'completed'       => $lesson->completed_count,
            'started'         => $lesson->started_count,
        ]);

        $quizStats = UserQuizAttempt::query()
            ->when($startDate, fn($q) => $q->whereDate('completed_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('completed_at', '<=', $endDate))
            ->selectRaw('
                AVG(score) as avg_score,
                AVG(completion_time) as avg_time,
                COUNT(CASE WHEN passed = 1 THEN 1 END) as passed_count,
                COUNT(CASE WHEN passed = 0 THEN 1 END) as failed_count
            ')
            ->first();

        $simulationStats = SimulationAttempt::whereNotNull('completed_at')
            ->when($startDate, fn($q) => $q->whereDate('completed_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('completed_at', '<=', $endDate))
            ->selectRaw('
                AVG(score / total_scenarios * 100) as avg_percentage,
                AVG(time_taken) as avg_time,
                COUNT(CASE WHEN (score / total_scenarios * 100) >= 70 THEN 1 END) as passed_count,
                COUNT(CASE WHEN (score / total_scenarios * 100) < 70 THEN 1 END) as failed_count
            ')
            ->first();

        $timeSpentPerLesson = Lesson::where('is_active', true)
            ->select('lessons.id', 'lessons.title')
            ->selectRaw('
                (
                    SELECT AVG(completion_time)
                    FROM user_quiz_attempts
                    JOIN quizzes ON user_quiz_attempts.quiz_id = quizzes.id
                    WHERE quizzes.lesson_id = lessons.id
                    AND user_quiz_attempts.completed_at IS NOT NULL
                ) as quiz_time,
                (
                    SELECT AVG(time_taken)
                    FROM simulation_attempts
                    WHERE lesson_id = lessons.id
                    AND completed_at IS NOT NULL
                ) as sim_time
            ')
            ->get()
            ->map(function ($lesson) {
                $quizTime = $lesson->quiz_time ?? 0;
                $simTime = $lesson->sim_time ?? 0;
                $avgTime = ($quizTime + $simTime) / 2;

                return [
                    'id'               => $lesson->id,
                    'title'            => $lesson->title,
                    'avg_time_seconds' => $avgTime,
                ];
            });

        return view('admin.analytics.overview', compact(
            'stats',
            'lessonCompletionRates',
            'quizStats',
            'simulationStats',
            'timeSpentPerLesson'
        ));
    }

    /**
     * Quiz Analytics with Question Difficulty
     */
    public function quizAnalytics(Request $request)
    {
        $lessonId = $request->input('lesson_id');
        $lessons = Lesson::where('is_active', true)->get();

        $attempts = UserQuizAttempt::whereNotNull('answers_data')
            ->when($lessonId, fn($q) => $q->whereHas('quiz', fn($sq) => $sq->where('lesson_id', $lessonId)))
            ->get();

        $questionStats = $this->collectQuestionStats($attempts);
        $difficultyData = $this->buildDifficultyArray($questionStats, 'question');

        // Use the correct variable name based on whether a lesson was selected
        $questionDifficulty = $lessonId ? $difficultyData : [];
        $aggregatedQuestions = $lessonId ? [] : $difficultyData;

        return view('admin.analytics.quiz', compact(
            'lessons',
            'lessonId',
            'questionDifficulty',
            'aggregatedQuestions'
        ));
    }

    /**
     * Simulation Analytics with CTR and Scenario Difficulty
     */
    public function simulationAnalytics(Request $request)
    {
        $lessonId = $request->input('lesson_id');
        $lessons = Lesson::where('is_active', true)->where('has_simulation', true)->get();

        $attempts = SimulationAttempt::whereNotNull('completed_at')
            ->when($lessonId, fn($q) => $q->where('lesson_id', $lessonId))
            ->get();

        $scenarioStats = $this->collectScenarioStats($attempts);
        $difficultyData = $this->buildDifficultyArray($scenarioStats, 'scenario');
        $ctrData = $this->collectClickData($attempts);

        $scenarioDifficulty = $lessonId ? $difficultyData : [];
        $aggregatedScenarios = $lessonId ? [] : $difficultyData;

        return view('admin.analytics.simulation', compact(
            'lessons',
            'lessonId',
            'scenarioDifficulty',
            'aggregatedScenarios',
            'ctrData'
        ));
    }

    /**
     * Difficulty Heatmap
     */
    public function heatmap(Request $request)
    {
        $quizHeatmap = $this->getQuizHeatmapData();
        $simulationHeatmap = $this->getSimulationHeatmapData();

        return view('admin.analytics.heatmap', compact('quizHeatmap', 'simulationHeatmap'));
    }

    /**
     * Export Analytics Data
     */
    public function export(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'quiz') {
            return $this->exportQuizAnalytics($request);
        } elseif ($type === 'simulation') {
            return $this->exportSimulationAnalytics($request);
        }

        return $this->exportOverview($request);
    }

    // ─── Private Helpers: Statistics Collection ─────────────────────────

    /**
     * Collect per-question statistics from quiz attempts.
     *
     * @return array<int, array{question_text: string, total: int, correct: int}>
     */
    private function collectQuestionStats(Collection $attempts): array
    {
        $stats = [];

        foreach ($attempts as $attempt) {
            $results = json_decode($attempt->answers_data, true);
            if (!is_array($results)) {
                continue;
            }

            foreach ($results as $result) {
                $qId = $result['question_id'];
                if (!isset($stats[$qId])) {
                    $stats[$qId] = [
                        'question_text' => $result['question_text'],
                        'total'         => 0,
                        'correct'       => 0,
                    ];
                }
                $stats[$qId]['total']++;
                if ($result['is_correct']) {
                    $stats[$qId]['correct']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Collect per-scenario statistics from simulation attempts.
     *
     * @return array<string, array{total: int, correct: int}>
     */
    private function collectScenarioStats(Collection $attempts): array
    {
        $stats = [];

        foreach ($attempts as $attempt) {
            if (!is_array($attempt->scenario_results)) {
                continue;
            }

            foreach ($attempt->scenario_results as $result) {
                $scenario = $result['scenario'];
                if (!isset($stats[$scenario])) {
                    $stats[$scenario] = ['total' => 0, 'correct' => 0];
                }
                $stats[$scenario]['total']++;
                if ($result['correct'] === true || $result['correct'] === 'true') {
                    $stats[$scenario]['correct']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Collect click-through rate data from simulation attempts.
     */
    private function collectClickData(Collection $attempts): array
    {
        $totalClicks = 0;
        $actionMenuClicks = 0;

        foreach ($attempts as $attempt) {
            if (!is_array($attempt->click_data)) {
                continue;
            }
            foreach ($attempt->click_data as $click) {
                $totalClicks++;
                if (isset($click['action']) && $click['action'] === 'opened_action_menu') {
                    $actionMenuClicks++;
                }
            }
        }

        return [
            'total_clicks'       => $totalClicks,
            'action_menu_clicks' => $actionMenuClicks,
            'ctr'                => $totalClicks > 0 ? round(($actionMenuClicks / $totalClicks) * 100, 2) : 0,
        ];
    }

    // ─── Private Helpers: Formatting ────────────────────────────────────

    /**
     * Build a sorted difficulty array from raw stats.
     *
     * @param  array   $stats    Collected stats keyed by ID/name
     * @param  string  $type     'question' or 'scenario'
     */
    private function buildDifficultyArray(array $stats, string $type): array
    {
        $data = [];
        $isQuestion = $type === 'question';

        foreach ($stats as $key => $stat) {
            $successRate = $stat['total'] > 0
                ? round(($stat['correct'] / $stat['total']) * 100, 2)
                : 0;

            $entry = [
                'success_rate'     => $successRate,
                'total_attempts'   => $stat['total'],
                'correct_attempts' => $stat['correct'],
                'difficulty_level' => $this->getDifficultyLevel($successRate),
            ];

            if ($isQuestion) {
                $entry['question_id'] = $key;
                $entry['question_text'] = $stat['question_text'];
            } else {
                $entry['scenario'] = $key;
            }

            $data[] = $entry;
        }

        // Sort by difficulty (lowest success rate = hardest first)
        usort($data, fn($a, $b) => $a['success_rate'] <=> $b['success_rate']);

        return $data;
    }

    /**
     * Map a success rate percentage to a human-readable difficulty label.
     */
    private function getDifficultyLevel(float $successRate): string
    {
        foreach (self::DIFFICULTY_THRESHOLDS as $threshold => $label) {
            if ($successRate >= $threshold) {
                return $label;
            }
        }

        return self::DIFFICULTY_DEFAULT;
    }

    // ─── Private Helpers: Heatmap ───────────────────────────────────────

    private function getQuizHeatmapData(): array
    {
        $attempts = UserQuizAttempt::whereNotNull('answers_data')->get();
        $stats = $this->collectQuestionStats($attempts);

        return array_values(array_map(fn($qId, $stat) => [
            'id'    => $qId,
            'label' => substr($stat['question_text'], 0, 50) . '...',
            'value' => $stat['total'] > 0
                ? round(($stat['correct'] / $stat['total']) * 100, 2)
                : 0,
        ], array_keys($stats), $stats));
    }

    private function getSimulationHeatmapData(): array
    {
        $attempts = SimulationAttempt::whereNotNull('completed_at')->get();
        $stats = $this->collectScenarioStats($attempts);

        return array_values(array_map(fn($scenario, $stat) => [
            'label' => $scenario,
            'value' => $stat['total'] > 0
                ? round(($stat['correct'] / $stat['total']) * 100, 2)
                : 0,
        ], array_keys($stats), $stats));
    }

    // ─── Private Helpers: Export ─────────────────────────────────────────

    private function exportOverview(Request $request)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics_overview.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Metric', 'Value']);

            // TODO: Add data rows for overview export
            fputcsv($file, ['Total Users', User::where('user_type', 'USER')->count()]);
            fputcsv($file, ['Total Active Lessons', Lesson::where('is_active', true)->count()]);
            fputcsv($file, ['Total Quiz Attempts', UserQuizAttempt::count()]);
            fputcsv($file, ['Total Simulation Attempts', SimulationAttempt::whereNotNull('completed_at')->count()]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * @todo Implement detailed quiz analytics CSV export
     */
    private function exportQuizAnalytics(Request $request)
    {
        // TODO: Implement quiz analytics export
    }

    /**
     * @todo Implement detailed simulation analytics CSV export
     */
    private function exportSimulationAnalytics(Request $request)
    {
        // TODO: Implement simulation analytics export
    }
}
