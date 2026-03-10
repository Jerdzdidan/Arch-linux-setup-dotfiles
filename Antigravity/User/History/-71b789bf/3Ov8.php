<?php

namespace App\Http\Controllers\AdminPanel;

use App\Events\StudentAcademicProgressCreate;
use App\Events\StudentCreationEvent;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class StudentUserController extends Controller
{
    //
    public function index()
    {
        return view('app.admin_panel.user_management.student_accounts.index');
    }

    public function getData(Request $request)
    {
        $students = Student::with(['user:id,name,email,status', 'program:id,name,code', 'curriculum:id,year_start,year_end'])
            ->select(['id', 'user_id', 'student_number', 'program_id', 'curriculum_id', 'year_level']);

        if ($request->filled('status') && $request->status !== 'All') {
            $isActive = $request->status === 'Active';
            $students->whereHas('user', function($query) use ($isActive) {
                $query->where('status', $isActive);
            });
        }
    
        return DataTables::of($students)
            ->addColumn('curriculum', function($row) {
                return $row->program->code . ' - Curriculum (' . $row->curriculum->year_start . '-' . $row->curriculum->year_end . ')';
            })
            ->editColumn('id', function ($row) {
                return Crypt::encryptString($row->id);
            })
            ->editColumn('user_id', function ($row) {
                return Crypt::encryptString($row->user_id);
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        
        $validated = $request->validate([
            // Student info
            'student_number' => 'required|string|unique:students,student_number',
            'program_id' => 'required|exists:programs,id',
            'curriculum_id' => 'required|exists:curricula,id',
            'year_level' => 'required|integer|min:1|max:10',
            // User info
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        try{
            DB::beginTransaction();
            
            $student = new Student();
            $student->student_number = $validated['student_number'];
            $student->program_id = $validated['program_id'];
            $student->curriculum_id = $validated['curriculum_id'];
            $student->year_level = $validated['year_level'];
            $student->save();

            event(new StudentCreationEvent($student, [
                'name' => $validated['name'],
                'password' => $validated['password']
            ]));

            event(new StudentAcademicProgressCreate($student));
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id) 
    {
        $decrypted = Crypt::decryptString($id);

        $student_profile = Student::findOrFail($decrypted);

        return response()->json([
            'id' => Crypt::encryptString($student_profile->id),
            'name' => $student_profile->user->name ?? null,
            'student_number' => $student_profile->student_number,
            'program_id' => $student_profile->program_id,
            'curriculum_id' => $student_profile->curriculum_id,
            'year_level' => $student_profile->year_level,
        ]);
    }

    public function update(Request $request, $id)
    {
        $decrypted = Crypt::decryptString($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'student_number' => 'required|string|unique:students,student_number,' . $decrypted,
            'year_level' => 'required|string',
            'program_id' => 'required|exists:programs,id',
            'curriculum_id' => 'required|exists:curricula,id',
        ]);

        $student = Student::findOrFail($decrypted);
        $student->student_number = $validated['student_number'];
        $student->program_id = $validated['program_id'];
        $student->curriculum_id = $validated['curriculum_id'];
        $student->year_level = $validated['year_level'];

        $student->user->name = $validated['name'];
        if (!empty($validated['password'])) {
            $student->user->password = Hash::make($validated['password']);
        }

        $student->save();
        $student->user->save();
    }

    public function destroy($id)
    {
        $decrypted = Crypt::decryptString($id);
        $student = Student::findOrFail($decrypted);

        DB::transaction(function() use ($student) {
            $student->user->delete();
            $student->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Officer deleted successfully.'
        ]);
    }

    public function importStudents(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:10240',
            ]);

            $import = new StudentsImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'imported_count' => $import->getImportedCount(),
                'failed_count' => $import->getFailedCount(),
                'failed_rows' => $import->getFailedRows(),
                'message' => $import->getImportedCount() . ' student(s) imported successfully.' .
                    ($import->getFailedCount() > 0 ? ' ' . $import->getFailedCount() . ' row(s) failed.' : ''),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->validator->errors()->toArray()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
