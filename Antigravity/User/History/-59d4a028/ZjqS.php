<?php

namespace App\Imports;

use App\Events\StudentAcademicProgressCreate;
use App\Events\StudentCreationEvent;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    private int $importedCount = 0;
    private int $failedCount = 0;
    private array $failedRows = [];
    private int $currentRow = 1; // heading row is row 0

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->currentRow++;
            $errors = [];
            $program = null;
            $yearLevel = null;

            // Validate student number
            $studentNumber = $row['student_number'] ?? null;
            if (!$studentNumber) {
                $errors[] = 'Student number is required';
            } else {
                if (!preg_match('/^\d{2}-\d{5}$/', $studentNumber)) {
                    $errors[] = 'Student number format must be nn-nnnnn (e.g., 23-12345)';
                } else {
                    $existing = Student::where('student_number', $studentNumber)->first();
                    if ($existing) {
                        $errors[] = 'Student already exists';
                    } else {
                        // Calculate year level
                        $year = now()->year;
                        $lastTwo = (int) substr($year, -2);
                        $firstTwo = (int) substr($studentNumber, 0, 2);
                        $yearLevel = ($lastTwo - $firstTwo > 5) ? 5 : max(1, $lastTwo - $firstTwo);
                    }
                }
            }

            // Validate name
            $name = $row['name'] ?? null;
            if (!$name) {
                $errors[] = 'Name is required';
            }

            // Validate program
            $programCode = $row['program_code'] ?? null;
            if (!$programCode) {
                $errors[] = 'Program code is required';
            } else {
                $program = Program::where('code', $programCode)->first();
                if (!$program) {
                    $errors[] = "Program '{$programCode}' not found";
                }
            }

            // If valid, create student directly
            if (empty($errors)) {
                try {
                    DB::beginTransaction();

                    $curriculum = $program->curriculum;

                    $student = new Student();
                    $student->student_number = $studentNumber;
                    $student->program_id = $program->id;
                    $student->curriculum_id = $curriculum ? $curriculum->id : null;
                    $student->year_level = $yearLevel;
                    $student->save();

                    // Fire events (creates user account + academic progress)
                    event(new StudentCreationEvent($student, [
                        'name' => $name,
                        'password' => $studentNumber, // default password = student number
                    ]));

                    event(new StudentAcademicProgressCreate($student));

                    DB::commit();
                    $this->importedCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->failedCount++;
                    $this->failedRows[] = [
                        'row' => $this->currentRow,
                        'student_number' => $studentNumber,
                        'name' => $name,
                        'program_code' => $programCode,
                        'errors' => ['Failed to create: ' . $e->getMessage()],
                    ];
                }
            } else {
                // Invalid row — collect for display
                $this->failedCount++;
                $this->failedRows[] = [
                    'row' => $this->currentRow,
                    'student_number' => $studentNumber ?? '',
                    'name' => $name ?? '',
                    'program_code' => $programCode ?? '',
                    'errors' => $errors,
                ];
            }
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }
}
