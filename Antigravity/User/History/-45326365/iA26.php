<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\StudentImport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StudentImportController extends Controller
{
    public function index() {
        return view('app.admin_panel.student_import_management.index');
    }

    public function getData()
    {
        $student_imports = StudentImport::get();

        return DataTables::of($student_imports)
            ->editColumn('id', function ($row) {
                return Crypt::encryptString($row->id);
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            // FIXED VALIDATION - more flexible
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls,txt|mimetypes:text/plain,text/csv,text/x-csv,application/csv,application/x-csv,text/comma-separated-values,text/x-comma-separated-values,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240',
            ]);

            $file = $request->file('file');

            // Check if filename already exists
            $existingImport = StudentImport::where('filename', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->first();

            if ($existingImport) {
                return response()->json([
                    'errors' => [
                        'filename' => ['A file with this name has already been imported.']
                    ],
                ], 422);
            }

            DB::beginTransaction();

            // Create import record
            $import = StudentImport::create([
                'filename' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'status' => 'pending',
            ]);

            Excel::import(new StudentsImport($import->id), $file);

            $import->refresh();

            $rowsCount = $import->rows()->count();
            $validCount = $import->rows()->where('validity', 'valid')->count();
            $invalidCount = $import->rows()->where('validity', 'invalid')->count();

            $import->update([
                'total_rows' => $rowsCount,
                'valid_rows' => $validCount,
                'invalid_rows' => $invalidCount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'import_id' => $import->id,
                'import' => $import->fresh()
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->validator->errors()->toArray()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $decrypted = Crypt::decryptString($id);
        $student_import = StudentImport::findOrFail($decrypted);

        $rows = $student_import->rows;

        $filename = $student_import->filename . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'student_number',
                'name',
                'program_code',
                'program_name',
                'year_level',
            ]);

            // CSV Data
            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->student_number,
                    $row->name,
                    $row->program_code,
                    $row->program_name,
                    $row->year_level,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function edit($student_import_id)
    {
        $decrypted = Crypt::decryptString($student_import_id);

        $student_import = StudentImport::findOrFail($decrypted);

        return response()->json([
            'id' => Crypt::encryptString($student_import->id),
            'filename' => $student_import->filename,
        ]);
    }

    public function update(Request $request, $student_import_id)
    {
        $decrypted = Crypt::decryptString($student_import_id);
        $student_import = StudentImport::findOrFail($decrypted);

        $validated = $request->validate([
            'filename' => 'required|string|max:255|unique:student_imports,filename,' . $decrypted,
        ]);

        $student_import->update([
            'filename' => $validated['filename'],
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($student_import)
    {
        $decrypted = Crypt::decryptString($student_import);
        StudentImport::findOrFail($decrypted)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student import record deleted successfully.'
        ]);
    }
}
