<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'is_pinned',
        'posted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * The user who posted this announcement.
     */
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
