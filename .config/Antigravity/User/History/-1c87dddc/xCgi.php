<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'difficulty',
        'description',
        'time',
        'content',
        'is_active',
        'prerequisite_lesson_id',
        'has_simulation'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_simulation' => 'boolean',
    ];

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function prerequisiteLesson()
    {
        return $this->belongsTo(Lesson::class, 'prerequisite_lesson_id');
    }

    public function dependentLessons()
    {
        return $this->hasMany(Lesson::class, 'prerequisite_lesson_id');
    }

    public function studentLessons()
    {
        return $this->hasMany(StudentLesson::class);
    }

    /**
     * Get the current user's progress for this lesson
     */
    public function progress()
    {
        return $this->hasOne(StudentLesson::class)->where('user_id', Auth::id());
    }

    /**
     * Get student lesson progress for current user
     */
    public function getStudentProgress()
    {
        if (!Auth::check()) {
            return null;
        }

        return StudentLesson::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $this->id
            ],
            [
                'is_unlocked' => $this->prerequisite_lesson_id === null,
                'content_viewed' => false,
                'quiz_passed' => false
            ]
        );
    }

    /**
     * Check if lesson is unlocked for current user
     */
    public function isUnlocked(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $progress = $this->getStudentProgress();
        return $progress->is_unlocked;
    }

    /**
     * Check if lesson is completed for current user
     */
    public function isCompleted(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $progress = $this->getStudentProgress();
        return $progress->isCompleted();
    }

    /**
     * Check if prerequisite is completed
     */
    public function isPrerequisiteCompleted(): bool
    {
        if (!$this->prerequisite_lesson_id) {
            return true;
        }

        if (!Auth::check()) {
            return false;
        }

        $prerequisiteProgress = StudentLesson::where('user_id', Auth::id())
            ->where('lesson_id', $this->prerequisite_lesson_id)
            ->first();

        return $prerequisiteProgress && $prerequisiteProgress->isCompleted();
    }

    /**
     * Unlock this lesson for a user
     */
    public function unlock($userId = null): void
    {
        $userId = $userId ?? Auth::id();

        StudentLesson::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $this->id
            ],
            [
                'is_unlocked' => true
            ]
        );
    }

    /**
     * Unlock dependent lessons if this lesson is completed
     */
    public function unlockDependentLessons($userId = null): void
    {
        $userId = $userId ?? Auth::id();

        // Check if this lesson is completed
        $progress = StudentLesson::where('user_id', $userId)
            ->where('lesson_id', $this->id)
            ->first();

        if ($progress && $progress->isCompleted()) {
            // Unlock all lessons that have this as prerequisite
            foreach ($this->dependentLessons as $dependentLesson) {
                $dependentLesson->unlock($userId);
            }
        }
    }
}
