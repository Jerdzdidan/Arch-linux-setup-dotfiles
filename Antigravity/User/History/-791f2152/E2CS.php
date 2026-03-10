<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'simulation_id',
        'started_at',
        'completed_at',
        'score',
        'total_scenarios',
        'time_taken',
        'click_data',
        'scenario_results',
        'attempt_number'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'click_data' => 'array',
        'scenario_results' => 'array',
        'score' => 'integer',
        'total_scenarios' => 'integer',
        'time_taken' => 'integer',
        'attempt_number' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Check if simulation was passed (70% or higher)
     */
    public function isPassed(): bool
    {
        if ($this->total_scenarios === 0) {
            return false;
        }

        return ($this->score / $this->total_scenarios) * 100 >= 70;
    }

    /**
     * Get percentage score
     */
    public function getPercentage(): float
    {
        if ($this->total_scenarios === 0) {
            return 0;
        }
        return round(($this->score / $this->total_scenarios) * 100, 2);
    }
}
