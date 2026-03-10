<?php

namespace App\Services;

use App\Models\StudentImport;
use App\Models\StudentImportRow;
use Illuminate\Support\Collection;

class StudentImportRowValidator
{
    /**
     * Validate a grade import row
     *
     * @param StudentImportRow $row
     * @param Collection $students Keyed by student_number
     * @param Collection $programs Keyed by code
     * @param StudentImport $student_import
     * @return array Array of error messages
     */
    public function validateRow(
        StudentImportRow $row,
        Collection $students,
        Collection $programs,
        StudentImport $student_import
    ): array {
        $errors = [];

        // Validate student
        $errors = array_merge($errors, $this->validateStudent($row, $students));

        // Validate name
        $errors = array_merge($errors, $this->validateName($row));

        // Validate program
        $errors = array_merge($errors, $this->validateProgram($row, $programs));

        // Validate duplicate entry
        $errors = array_merge($errors, $this->validateDuplicateEntry($row));

        return $errors;
    }

    /**
     * Validate student exists
     */
    protected function validateStudent(StudentImportRow $row, Collection $students): array
    {
        $errors = [];

        if (empty($row->student_number)) {
            $errors[] = 'Student number is required';
            return $errors;
        } else {
            $student = $students->get($row->student_number);

            if ($student) {
                $errors[] = 'Student4 already exists';
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
        }

        return $errors;
    }

    /**
     * Validate subject exists
     */
    protected function validateName(StudentImportRow $row): array
    {
        $errors = [];

        if (empty($row->name)) {
            $errors[] = 'Name is required';
            return $errors;
        }

        return $errors;
    }

    protected function validateProgram(StudentImportRow $row, Collection $programs): array
    {
        $errors = [];

        if (empty($row->program_code)) {
            $errors[] = 'Program code is required';
            return $errors;
        }

        $program = $programs->get($row->program_code);

        if (!$program) {
            $row->program_code = null;
            $errors[] = 'Program not found';
        } else {
            $row->program_code = $program->code;
            $row->subject_name = $program->name;
        }

        return $errors;
    }

    /**
     * Check for duplicate entries
     */
    protected function validateDuplicateEntry(StudentImportRow $row): array
    {
        $errors = [];

        if (empty($row->student_number) || empty($row->program_code)) {
            return $errors; // Skip duplicate check if required fields are missing
        }

        // Find all rows with same key fields
        $duplicates = StudentImportRow::where('student_number', $row->student_number)
            ->where('id', '!=', $row->id)
            ->get();

        if ($duplicates->isNotEmpty()) {
            // Mark all duplicates as invalid
            foreach ($duplicates as $duplicate) {
                $duplicate->validity = 'invalid';
                $duplicate->errors = json_encode(['Duplicate entry for the same student']);

                $duplicate->status = 'staged';

                $duplicate->save();
            }

            $errors[] = 'Duplicate entry for the same student and subject';
        }

        return $errors;
    }
}
