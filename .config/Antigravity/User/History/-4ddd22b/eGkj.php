<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentImport extends Model
{
    //

    protected $fillable = [
        'filename',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'status',
        'processed_at',
    ];

    public function rows(){
        return $this->hasMany(StudentImportRow::class);
    }
}
