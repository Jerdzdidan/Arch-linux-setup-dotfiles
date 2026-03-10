<?php

namespace App\Services;

use App\Models\StudentImport;
use App\Models\StudentImportRow;
use Illuminate\Support\Collection;

class StudentImportRowValidator
{
    private const STUDENT_NUMBER_PATTERN = '/^\d{2}-\d{5}$/';
    private const MAX_YEAR_LEVEL = 5;
    private const MIN_YEAR_LEVEL = 1;

    /**
     * Validate a student import row.
     *
     * @param StudentImportRow $row
     * @param Collection $students Keyed by student_number
     * @param Collection $programs Keyed by code
     * @param StudentImport $studentImport
     * @return array<string> Array of error messages
     */
    public function validateRow(
        StudentImportRow $row,
        Collection $students,
        Collection $programs,
        StudentImport $studentImport
    ): array {
        $errors = [];

        $errors = array_merge($errors, $this->validateStudent($row, $students));
        $errors = array_merge($errors, $this->validateName($row));
        $errors = array_merge($errors, $this->validateProgram($row, $programs));
        $errors = array_merge($errors, $this->validateDuplicateEntry($row, $studentImport));

        return $errors;
    }

    /**
     * Validate student number format and determine year level.
     *
     * @param StudentImportRow $row
     * @param Collection $students Existing students keyed by student_number
     * @return array<string>
     */
    protected function validateStudent(StudentImportRow $row, Collection $students): array
    {
        if (empty($row->student_number)) {
            return ['Student number is required'];
        }

        if ($students->has($row->student_number)) {
            return ['Student already exists'];
        }

        if (!preg_match(self::STUDENT_NUMBER_PATTERN, $row->student_number)) {
            return ['Student number format must be nn-nnnnn (e.g., 23-12345)'];
        }

        $row->year_level = $this->calculateYearLevel($row->student_number);

        return [];
    }

    /**
     * Calculate the year level from a student number.
     *
     * @param string $studentNumber Format: nn-nnnnn
     * @return int Year level clamped between MIN_YEAR_LEVEL and MAX_YEAR_LEVEL
     */
    private function calculateYearLevel(string $studentNumber): int
    {
        $currentYearLastTwo = (int) now()->format('y');
        $enrollmentYear = (int) substr($studentNumber, 0, 2);

        return max(self::MIN_YEAR_LEVEL, min(self::MAX_YEAR_LEVEL, $currentYearLastTwo - $enrollmentYear));
    }

    /**
     * Validate that the student name is present.
     *
     * @param StudentImportRow $row
     * @return array<string>
     */
    protected function validateName(StudentImportRow $row): array
    {
        if (empty($row->name)) {
            return ['Name is required'];
        }

        return [];
    }

    /**
     * Validate that the program code exists and enrich the row with program data.
     *
     * @param StudentImportRow $row
     * @param Collection $programs Existing programs keyed by code
     * @return array<string>
     */
    protected function validateProgram(StudentImportRow $row, Collection $programs): array
    {
        if (empty($row->program_code)) {
            return ['Program code is required'];
        }

        $program = $programs->get($row->program_code);

        if (!$program) {
            $row->program_code = null;
            return ['Program not found'];
        }

        $row->program_code = $program->code;
        $row->subject_name = $program->name;

        return [];
    }

    /**
     * Check for duplicate student entries within the same import batch.
     *
     * @param StudentImportRow $row
     * @param StudentImport $studentImport
     * @return array<string>
     */
    protected function validateDuplicateEntry(StudentImportRow $row, StudentImport $studentImport): array
    {
        if (empty($row->student_number) || empty($row->program_code)) {
            return [];
        }

        $duplicates = StudentImportRow::where('student_import_id', $studentImport->id)
            ->where('student_number', $row->student_number)
            ->where('id', '!=', $row->id)
            ->get();

        if ($duplicates->isEmpty()) {
            return [];
        }

        $duplicateErrors = json_encode(['Duplicate entry for the same student']);

        $duplicates->each(function (StudentImportRow $duplicate) use ($duplicateErrors) {
            $duplicate->update([
                'validity' => 'invalid',
                'errors'   => $duplicateErrors,
                'status'   => 'staged',
            ]);
        });

        return ['Duplicate entry for the same student'];
    }
}
