<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Student;
use App\Models\StudentImport;
use App\Models\StudentImportRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class StudentsImport implements ToCollection, WithHeadingRow
{
    private $student_import_id;

    public function __construct(int $student_import_id)
    {
        $this->student_import_id = $student_import_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $errors = [];
            $student = null;
            $program = null;

            if (isset($row['student_number'])) {
                $student = Student::where('student_number', $row['student_number'])
                    ->first();
                if ($student) {
                    $errors[] = 'Student already exists';
                } else {
                    if (!preg_match('/^\d{2}-\d{5}$/', $row['student_number'])) {
                        $errors[] = 'Student number format must be nn-nnnnn (e.g., 23-12345)';
                    } else {
                        $year = now()->year;
                        $lastTwo = (int) substr($year, -2);

                        $firstTwo = (int) substr($row['student_number'], 0, 2);

                        if ($lastTwo - $firstTwo > 5) {
                            $row['year_level'] = 5;
                        } else {
                            $row['year_level'] = $lastTwo - $firstTwo;
                        }
                    }
                }
            } else {
                $errors[] = 'Student number is required';
            }

            if (!isset($row['name'])) {
                $errors[] = 'Name is required';
            }

            if (isset($row['program_code'])) {
                $program = Program::where('code', $row['program_code'])->first();

                if (!$program) {
                    $errors[] = 'Program not found';
                } else {
                    $row['program_name'] = $program->name;
                }
            } else {
                $errors[] = 'Program code is required';
            }

            // Create import row
            StudentImportRow::create([
                'student_import_id' => $this->student_import_id,
                'student_number' => $row['student_number'] ?? '',
                'name' => $row['name'] ?? null,
                'program_code' => $row['program_code'] ?? null,
                'program_name' => $row['program_name'] ?? null,
                'year_level' => $row['year_level'] ?? null,
                'validity' =>  empty($errors) ? 'valid' : 'invalid',
                'status' => 'staged',
                'errors' => !empty($errors) ? json_encode($errors) : null,
            ]);
        }
    }
}
