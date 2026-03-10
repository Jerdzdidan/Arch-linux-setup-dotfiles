<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'recipient_type',
        'filters',
        'sent_by',
        'recipients_count',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    /**
     * The user who sent this announcement.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Build a query for the recipients based on recipient_type and filters.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildRecipientsQuery()
    {
        $query = User::whereNotNull('email')->where('email', '!=', '');

        // Filter by recipient type
        switch ($this->recipient_type) {
            case 'students':
                $query->where('user_type', 'STUDENT');
                break;
            case 'officers':
                $query->where('user_type', 'OFFICER');
                break;
            case 'admins':
                $query->where('user_type', 'ADMIN');
                break;
            case 'all':
            default:
                // No user_type filter — send to everyone
                break;
        }

        // Apply optional filters (program_id, year_level) — only relevant for students
        $filters = $this->filters ?? [];

        if (!empty($filters['program_id'])) {
            $programId = $filters['program_id'];
            $query->whereHas('student', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }

        if (!empty($filters['year_level'])) {
            $yearLevel = $filters['year_level'];
            $query->whereHas('student', function ($q) use ($yearLevel) {
                $q->where('year_level', $yearLevel);
            });
        }

        return $query;
    }
}
