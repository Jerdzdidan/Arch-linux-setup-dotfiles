<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Curriculum;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudentImport;
use App\Models\StudentImportRow;
use App\Services\StudentImportRowValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportRowController extends Controller
{
    // Status Constants
    const STATUS_STAGED = 'staged';
    const STATUS_COMMITTED = 'committed';

    // Validity Constants
    const VALIDITY_VALID = 'valid';
    const VALIDITY_INVALID = 'invalid';

    protected StudentImportRowValidator $validator;

    public function __construct(StudentImportRowValidator $validator)
    {
        $this->validator = $validator;
    }

    // Display the student import rows index page
    public function index(string $studentImportId)
    {
        try {
            $studentImport = $this->findStudentImport($studentImportId);

            // Validate all rows within a transaction
            DB::transaction(function () use ($studentImport) {
                $this->validateAllRows($studentImport);
            });

            // Update student import statistics
            $this->updateStudentImportStatistics($studentImport);

            $invalidCount = $studentImport->rows()->where('validity', $this::VALIDITY_INVALID)->count();
            $stagedCount = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count();

            $allCommited = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count() === 0;

            return view('app.admin_panel.student_import_management.student_import_rows.index', [
                'studentImportId' => Crypt::encryptString($studentImport->id),
                'studentImportName' => $studentImport->filename,
                'valid' => $invalidCount === 0,
                'allCommited' => $allCommited,
                'hasStagedData' => $stagedCount > 0,
            ]);
        } catch (Exception $e) {
            Log::error('Error in student import rows index', [
                'student_import_id' => $studentImportId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'An error occurred while loading student import rows.');
        }
    }

    // Get data for DataTables
    public function getData(Request $request, string $studentImportId): JsonResponse
    {
        try {
            $studentImport = $this->findStudentImport($studentImportId);
            $studentImportRows = $studentImport->rows();

            if ($request->filled('status') && $request->status !== 'All') {
                $studentImportRows->where('status', $request->status);
            }

            if ($request->filled('validity') && $request->validity !== 'All') {
                $studentImportRows->where('validity', $request->validity);
            }

            $studentImportRows = $studentImportRows->get();

            return datatables()->of($studentImportRows)
                ->editColumn('id', fn($row) => Crypt::encryptString($row->id))
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error fetching student import row data', [
                'student_import_id' => $studentImportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching data'
            ], 500);
        }
    }

    // Store a new student import row
    public function store(Request $request, string $studentImportId): JsonResponse
    {
        try {
            $studentImport = $this->findStudentImport($studentImportId);

            // Validate input
            $validated = $this->validateRequest($request);

            // Verify program exists
            $program = $this->findProgramOrFail($validated['program_code']);

            // Prepare data for creation
            $data = $this->prepareRowData($validated, $studentImport, $program);

            DB::transaction(function () use ($studentImport, $data) {
                // Create the new row
                $newRow = StudentImportRow::create($data);

                // Validate all rows including the new one
                $this->validateAllRows($studentImport);

                // Update statistics
                $this->updateStudentImportStatistics($studentImport);
            });

            $allValid = $studentImport->rows()->where('validity', $this::VALIDITY_INVALID)->count() === 0;
            $allCommited = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count() === 0;

            return response()->json(['success' => true, 'allValid' => $allValid, 'allCommited' => $allCommited]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error creating student import row', [
                'student_import_id' => $studentImportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request, string $studentImportId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls,txt|mimetypes:text/plain,text/csv,text/x-csv,application/csv,application/x-csv,text/comma-separated-values,text/x-comma-separated-values,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240',
            ]);

            $file = $validated['file'];
            $studentImport = $this->findStudentImport($studentImportId);
            Excel::import(new StudentsImport($studentImport->id), $file);

            // Validate all rows after import
            DB::transaction(function () use ($studentImport) {
                $this->validateAllRows($studentImport);
                $this->updateStudentImportStatistics($studentImport);
            });

            $allValid = $studentImport->rows()->where('validity', $this::VALIDITY_INVALID)->count() === 0;
            $allCommited = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count() === 0;

            return response()->json([
                'success' => true,
                'message' => 'File imported successfully',
                'allValid' => $allValid,
                'allCommited' => $allCommited
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error importing student import row', [
                'student_import_id' => $studentImportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get student import row for editing
    public function edit(string $studentImportRowId): JsonResponse
    {
        try {
            $row = $this->findStudentImportRow($studentImportRowId);

            return response()->json([
                'id' => Crypt::encryptString($row->id),
                'student_number' => $row->student_number,
                'name' => $row->name,
                'program_code' => $row->program_code,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching student import row for edit', [
                'student_import_row_id' => $studentImportRowId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Row not found'
            ], 404);
        }
    }

    // Update a student import row
    public function update(Request $request, string $studentImportRowId): JsonResponse
    {
        try {
            $row = $this->findStudentImportRow($studentImportRowId);

            // Validate input
            $validated = $this->validateRequest($request);

            // Verify program exists
            $program = $this->findProgramOrFail($validated['program_code']);

            DB::transaction(function () use ($row, $validated, $program) {
                // Update the row
                $row->update($validated);

                // Update program name
                $row->program_name = $program->name;
                $row->save();

                // Validate all rows in the import
                $this->validateAllRows($row->studentImport);

                // Update statistics
                $this->updateStudentImportStatistics($row->studentImport);
            });

            $studentImport = $row->fresh()->studentImport;
            $allValid = $studentImport->rows()->where('validity', $this::VALIDITY_INVALID)->count() === 0;
            $allCommited = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count() === 0;

            return response()->json([
                'success' => true,
                'allValid' => $allValid,
                'allCommited' => $allCommited
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error updating student import row', [
                'student_import_row_id' => $studentImportRowId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a student import row
    public function destroy(string $studentImportRowId): JsonResponse
    {
        try {
            $row = $this->findStudentImportRow($studentImportRowId);
            $studentImport = $row->studentImport;

            DB::transaction(function () use ($row, $studentImport) {
                // Update counts before deletion
                if ($row->validity === $this::VALIDITY_VALID) {
                    $studentImport->valid_rows = max(0, $studentImport->valid_rows - 1);
                } elseif ($row->validity === $this::VALIDITY_INVALID) {
                    $studentImport->invalid_rows = max(0, $studentImport->invalid_rows - 1);
                }

                $studentImport->total_rows = max(0, $studentImport->total_rows - 1);
                $studentImport->save();

                $row->delete();

                // Validate all remaining rows to ensure consistency
                $this->validateAllRows($studentImport);
                // Update statistics after validation
                $this->updateStudentImportStatistics($studentImport);
            });

            $allValid = $studentImport->rows()->where('validity', $this::VALIDITY_INVALID)->count() === 0;
            $allCommited = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count() === 0;

            return response()->json([
                'success' => true,
                'allValid' => $allValid,
                'allCommited' => $allCommited,
                'message' => 'Student data record deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting student import row', [
                'student_import_row_id' => $studentImportRowId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting record'
            ], 500);
        }
    }

    // Commit a single row to students table
    public function commitRow(string $studentImportRowId): JsonResponse
    {
        try {
            $row = $this->findStudentImportRow($studentImportRowId);

            if ($row->validity !== $this::VALIDITY_VALID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot commit invalid row.'
                ], 400);
            }

            DB::transaction(function () use ($row) {
                $program = Program::where('code', $row->program_code)->firstOrFail();
                $curriculum = $program->curriculum;

                Student::create([
                    'student_number' => $row->student_number,
                    'program_id' => $program->id,
                    'curriculum_id' => $curriculum ? $curriculum->id : null,
                    'year_level' => $row->year_level,
                ]);

                $row->status = $this::STATUS_COMMITTED;
                $row->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Student data row committed successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error committing student import row', [
                'student_import_row_id' => $studentImportRowId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error committing row'
            ], 500);
        }
    }

    // Commit all valid staged rows
    public function commitAll(string $studentImportId): JsonResponse
    {
        try {
            $studentImport = $this->findStudentImport($studentImportId);

            $rows = $studentImport->rows()
                ->where('status', $this::STATUS_STAGED)
                ->get();

            // Validate before committing
            if ($rows->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No staged rows to commit.'
                ], 400);
            }

            $invalidCount = $rows->where('validity', $this::VALIDITY_INVALID)->count();
            if ($invalidCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot commit all rows. There are invalid rows present.'
                ], 400);
            }

            // Commit all rows
            DB::transaction(function () use ($rows, $studentImport) {
                // Eager load programs to avoid N+1 queries
                $programCodes = $rows->pluck('program_code')->unique();
                $programs = Program::whereIn('code', $programCodes)
                    ->with('curriculum')
                    ->get()
                    ->keyBy('code');

                foreach ($rows as $row) {
                    $program = $programs->get($row->program_code);

                    if (!$program) {
                        throw new Exception("Program not found: {$row->program_code}");
                    }

                    $curriculum = $program->curriculum;

                    Student::create([
                        'student_number' => $row->student_number,
                        'program_id' => $program->id,
                        'curriculum_id' => $curriculum ? $curriculum->id : null,
                        'year_level' => $row->year_level,
                    ]);

                    $row->status = $this::STATUS_COMMITTED;
                    $row->save();
                }

                // Update student import status
                $stagedCount = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count();
                if ($stagedCount === 0) {
                    $studentImport->status = $this::STATUS_COMMITTED;
                }

                $studentImport->processed_at = now();
                $studentImport->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'All staged student data rows committed successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error committing all student import rows', [
                'student_import_id' => $studentImportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error committing rows: ' . $e->getMessage()
            ], 500);
        }
    }

    // Uncommit a row (revert from committed to staged)
    public function unCommit(string $studentImportRowId): JsonResponse
    {
        try {
            $row = $this->findStudentImportRow($studentImportRowId);

            DB::transaction(function () use ($row) {
                Student::where('student_number', $row->student_number)->delete();

                $row->status = $this::STATUS_STAGED;
                $row->save();

                // Update statistics
                $this->updateStudentImportStatistics($row->studentImport);
            });

            return response()->json([
                'success' => true,
                'message' => 'Student data row uncommitted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error uncommitting student import row', [
                'student_import_row_id' => $studentImportRowId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uncommitting row'
            ], 500);
        }
    }

    public function uncommitAll(string $studentImportId): JsonResponse
    {
        try {
            $studentImport = $this->findStudentImport($studentImportId);

            DB::transaction(function () use ($studentImport) {
                // Get all committed rows and delete corresponding students
                $committedRows = $studentImport->rows()
                    ->where('status', $this::STATUS_COMMITTED)
                    ->get();

                $studentNumbers = $committedRows->pluck('student_number')->unique();
                Student::whereIn('student_number', $studentNumbers)->delete();

                $studentImport->rows()
                    ->where('status', $this::STATUS_COMMITTED)
                    ->update(['status' => $this::STATUS_STAGED]);
            });

            $studentImport->processed_at = now();
            $studentImport->save();

            // Validate all remaining rows to ensure consistency
            $this->validateAllRows($studentImport);
            // Update statistics after validation
            $this->updateStudentImportStatistics($studentImport);

            return response()->json([
                'success' => true,
                'message' => 'All student data rows uncommitted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error uncommitting all student import rows', [
                'student_import_id' => $studentImportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uncommitting rows: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fetch validation errors for a row
    public function fetchErrors(string $studentImportRowId): JsonResponse
    {
        try {
            $row = $this->findStudentImportRow($studentImportRowId);

            $errors = $row->errors ? json_decode($row->errors, true) : [];

            return response()->json([
                'success' => true,
                'messages' => $errors
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching errors for student import row', [
                'student_import_row_id' => $studentImportRowId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => []
            ], 500);
        }
    }

    /**
     * Helper Methods
     */

    // Find student import by encrypted ID
    protected function findStudentImport(string $encryptedId): StudentImport
    {
        $decrypted = Crypt::decryptString($encryptedId);
        return StudentImport::findOrFail($decrypted);
    }

    // Find student import row by encrypted ID
    protected function findStudentImportRow(string $encryptedId): StudentImportRow
    {
        $decrypted = Crypt::decryptString($encryptedId);
        return StudentImportRow::findOrFail($decrypted);
    }

    // Validate all rows in a student import
    protected function validateAllRows(StudentImport $studentImport): void
    {
        $rows = $studentImport->rows()->get();

        // Eager load students and programs to avoid N+1 queries
        $studentNumbers = $rows->pluck('student_number')->unique()->filter();
        $programCodes = $rows->pluck('program_code')->unique()->filter();

        $students = Student::whereIn('student_number', $studentNumbers)
            ->get()
            ->keyBy('student_number');

        $programs = Program::whereIn('code', $programCodes)
            ->get()
            ->keyBy('code');

        foreach ($rows as $row) {
            $errors = $this->validator->validateRow(
                $row,
                $students,
                $programs,
                $studentImport
            );

            $row->validity = empty($errors) ? $this::VALIDITY_VALID : $this::VALIDITY_INVALID;
            $row->errors = json_encode($errors);
            $row->save();
        }
    }

    // Update student import statistics
    protected function updateStudentImportStatistics(StudentImport $studentImport): void
    {
        $studentImport->valid_rows = $studentImport->rows()
            ->where('validity', $this::VALIDITY_VALID)
            ->count();

        $studentImport->invalid_rows = $studentImport->rows()
            ->where('validity', $this::VALIDITY_INVALID)
            ->count();

        $studentImport->total_rows = $studentImport->rows()->count();
        $allCommited = $studentImport->rows()->where('status', $this::STATUS_STAGED)->count() === 0;

        if ($allCommited) {
            $studentImport->status = "committed";
        } else {
            $studentImport->status = "pending";
        }

        $studentImport->save();
    }

    // Validate request data
    protected function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'program_code' => 'required|string|max:50',
        ]);

        return $validated;
    }

    // Find program or throw exception
    protected function findProgramOrFail(string $programCode): Program
    {
        $program = Program::where('code', $programCode)->first();

        if (!$program) {
            throw ValidationException::withMessages([
                'program_code' => ['Program with this code does not exist.']
            ]);
        }

        return $program;
    }

    // Prepare row data for creation
    protected function prepareRowData(array $validated, StudentImport $studentImport, Program $program): array
    {
        $year = now()->year;
        $lastTwo = (int) substr($year, -2);
        $firstTwo = (int) substr($validated['student_number'], 0, 2);

        $yearLevel = ($lastTwo - $firstTwo > 5) ? 5 : ($lastTwo - $firstTwo);

        return array_merge($validated, [
            'program_name' => $program->name,
            'year_level' => $yearLevel,
            'validity' => $this::VALIDITY_VALID,
            'status' => $this::STATUS_STAGED,
            'student_import_id' => $studentImport->id,
        ]);
    }
}
