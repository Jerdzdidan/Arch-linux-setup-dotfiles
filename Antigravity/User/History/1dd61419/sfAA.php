<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentImportRow extends Model
{
    //
    protected $fillable = [
        'student_import_id',
        'student_number',
        'name',
        'program_code',
        'program_name',
        'year_level',
        'validity',
        'status',
        'errors',
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function studentImport(){
        return $this->belongsTo(StudentImport::class);
    }

    public function getErrorMessages()
    {
        return $this->errors ?? [];
    }
}
