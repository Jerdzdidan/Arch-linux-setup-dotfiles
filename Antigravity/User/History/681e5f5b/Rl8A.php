<?php

use App\Http\Controllers\AdminPanel\AcademicPeriodController;
use App\Http\Controllers\AdminPanel\AdminUserController;
use App\Http\Controllers\AdminPanel\CurriculumController;
use App\Http\Controllers\AdminPanel\DepartmentController;
use App\Http\Controllers\AdminPanel\GradeImportController;
use App\Http\Controllers\AdminPanel\GradeImportRowController;
use App\Http\Controllers\AdminPanel\StudentImportController;
use App\Http\Controllers\AdminPanel\StudentImportRowController;
use App\Http\Controllers\AdminPanel\OfficerUserController;
use App\Http\Controllers\AdminPanel\ProgramController;
use App\Http\Controllers\AdminPanel\StudentUserController;
use App\Http\Controllers\AdminPanel\SubjectController;
use App\Http\Controllers\AdminPanel\UserController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\GlobalLogoutController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\OfficerPanel\StudentAcademicProgressController;
use App\Http\Controllers\StudentPortal\AcademicProgress;
use App\Http\Controllers\StudentPortal\GradeController;
use App\Http\Middleware\PreventSelfAction;
use App\Http\Middleware\StudentInformationCheck;
use Illuminate\Support\Facades\Route;

// PLACEHOLDER ROUTE
Route::get('/placeholder', function () {
    return 'This page is under construction.';
})->name('#');

// HOME PAGE
Route::get('/', function () {
    return view('home');
})->middleware('auth')->name('home');

// DEFAULT ROUTE FOR AUTHENTICATION SELECTION
Route::view('auth', 'auth.index')->name('auth.index');

// ADMIN LOGIN/AUTHENTICATION
Route::prefix('auth/admin')->group(function () {
    Route::get('login', [AdminAuthController::class, 'index'])->name('auth.admin.login');
    Route::post('authenticate', [AdminAuthController::class, 'authenticate'])->name('auth.admin.authenticate');
});

// STUDENT LOGIN/AUTHENTICATION
Route::prefix('auth/student')->group(function () {
    Route::get('login', [StudentAuthController::class, 'index'])->name('auth.student.login');
    Route::post('authenticate', [StudentAuthController::class, 'authenticate'])->name('auth.student.authenticate');
});

// LOGOUT
Route::get('auth/logout/{user_type}', [GlobalLogoutController::class, 'logout'])->name('auth.logout');


// ADMIN PANEL
Route::prefix('admin')->middleware('auth')->can('is-admin')->group(function () {

    // USER MANAGEMENT
    Route::prefix('users')->group(function () {
        // ADMIN ACCOUNTS MANAGEMENT
        Route::resource('admins', AdminUserController::class);
        // E-R OFFICER ACCOUNTS MANAGEMENT
        Route::resource('officers', OfficerUserController::class);

        // STUDENT ACCOUNTS MANAGEMENT
        Route::get('students/data', [StudentUserController::class, 'getData'])->name('students.data');
        Route::resource('students', StudentUserController::class);

        // GENERIC USER ROUTES
        Route::get('data/{user_type}', [UserController::class, 'getData'])->name('users.data');
        Route::get('stats/{user_type}', [UserController::class, 'getStats'])->name('users.stats');
        Route::post('toggle-status/{id}', [UserController::class, 'toggle'])->middleware(PreventSelfAction::class)->name('users.toggle');
    });

    // ACADEMIC PERIOD MANAGEMENT
    Route::resource('academic_periods', AcademicPeriodController::class)->except(['show']);
    Route::prefix('academic_periods')->group(function () {
        Route::get('data', [AcademicPeriodController::class, 'getData'])->name('academic_periods.data');
        Route::get('stats', [AcademicPeriodController::class, 'getStats'])->name('academic_periods.stats');
        Route::post('toggle/{id}', [AcademicPeriodController::class, 'toggle'])->name('academic_periods.toggle');

        // FOR SELECT2
        Route::get('select', [AcademicPeriodController::class, 'getAcademicPeriodsForSelect'])->name('academic_periods.select');
    });

    // DEPARTMENTS MANAGEMENT
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::prefix('departments')->group(function () {

        Route::get('data', [DepartmentController::class, 'getData'])->name('departments.data');
        Route::get('stats', [DepartmentController::class, 'getStats'])->name('departments.stats');

        // FOR SELECT2
        Route::get('select', [DepartmentController::class, 'getDepartmentsForSelect'])->name('departments.select');
    });

    // PROGRAMS MANAGEMENT
    Route::resource('programs', ProgramController::class)->except(['show']);
    Route::prefix('programs')->group(function () {
        Route::get('data', [ProgramController::class, 'getData'])->name('programs.data');
        Route::get('stats', [ProgramController::class, 'getStats'])->name('programs.stats');

        // FOR SELECT2
        Route::get('select', [ProgramController::class, 'getProgramsForSelect'])->name('programs.select');
    });

    // CURRICULUM MANAGEMENT
    Route::resource('curricula', CurriculumController::class)->except(['show']);
    Route::prefix('curricula')->group(function () {
        Route::get('data', [CurriculumController::class, 'getData'])->name('curricula.data');
        Route::get('stats', [CurriculumController::class, 'getStats'])->name('curricula.stats');

        Route::post('toggle/{id}', [CurriculumController::class, 'toggle'])->name('curricula.toggle');

        Route::get('select/{program_id}', [CurriculumController::class, 'getCurriculaForSelectFiltered'])->name('curricula.select');

        // CURRICULUM SUBJECT MANAGEMENT
        Route::prefix('subjects')->group(function () {
            Route::get('/{curriculum_id}', [SubjectController::class, 'index'])->name('subjects.index');
            Route::get('data/{curriculum_id}', [SubjectController::class, 'getData'])->name('subjects.data');
            Route::get('stats/{curriculum_id}', [SubjectController::class, 'getStats'])->name('subjects.stats');
            Route::post('store/{curriculum_id}', [SubjectController::class, 'store'])->name('subjects.store');
            Route::get('edit/{id}', [SubjectController::class, 'edit'])->name('subjects.edit');
            Route::put('update/{id}', [SubjectController::class, 'update'])->name('subjects.update');
            Route::delete('destroy/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
            Route::post('toggle/{id}', [SubjectController::class, 'toggle'])->name('subjects.toggle');
        });
    });

    // GRADE IMPORT FEATURE
    Route::prefix('grades/import')->group(function () {
        // GRADE IMPORTS
        Route::get('/', [GradeImportController::class, 'index'])->name('grades.import.index');
        Route::get('data', [GradeImportController::class, 'getData'])->name('grades.import.data');
        Route::get('stats', [GradeImportController::class, 'getStats'])->name('grades.import.stats');
        Route::post('store', [GradeImportController::class, 'store'])->name('grades.import.store');
        Route::get('edit/{gradeImportId}', [GradeImportController::class, 'edit'])->name('grades.import.edit');
        Route::put('update/{gradeImportId}', [GradeImportController::class, 'update'])->name('grades.import.update');
        Route::get('download/{id}', [GradeImportController::class, 'download'])->name('grades.import.download');
        Route::delete('destroy/{gradeImport}', [GradeImportController::class, 'destroy'])->name('grades.import.destroy');

        // GRADE IMPORT ROWS
        Route::prefix('rows')->group(function () {
            Route::get('/{gradeImportId}', [GradeImportRowController::class, 'index'])->name('grades.import.rows.index');
            Route::get('data/{gradeImportId}', [GradeImportRowController::class, 'getData'])->name('grades.import.rows.data');

            Route::post('store/{gradeImportId}', [GradeImportRowController::class, 'store'])->name('grades.import.rows.store');
            Route::get('edit/{gradeImportRowId}', [GradeImportRowController::class, 'edit'])->name('grades.import.rows.edit');
            Route::put('update/{gradeImportRowId}', [GradeImportRowController::class, 'update'])->name('grades.import.rows.update');
            Route::delete('destroy/{gradeImportRowId}', [GradeImportRowController::class, 'destroy'])->name('grades.import.rows.destroy');

            Route::post('import/{gradeImportId}', [GradeImportRowController::class, 'import'])->name('grades.import.rows.import');

            Route::post('commit-row/{gradeImportRowId}', [GradeImportRowController::class, 'commitRow'])->name('grades.import.rows.commitRow');
            Route::post('commit-all/{gradeImportId}', [GradeImportRowController::class, 'commitAll'])->name('grades.import.rows.commitAll');

            Route::post('uncommitAll/{gradeImportRowId}', [GradeImportRowController::class, 'uncommitAll'])->name('grades.import.rows.uncommitAll');
            Route::post('uncommit/{gradeImportRowId}', [GradeImportRowController::class, 'unCommit'])->name('grades.import.rows.uncommit');

            Route::get('errors/{gradeImportRowId}', [GradeImportRowController::class, 'fetchErrors'])->name('grades.import.rows.errors');
        });

        Route::get('commit/{gradeImport}', [GradeImportController::class, 'commit'])->name('grades.import.commit');
    });

    // STUDENT IMPORT FEATURE
    Route::prefix('students/import')->group(function () {
        // STUDENT IMPORTS
        Route::get('/', [StudentImportController::class, 'index'])->name('students.import.index');
        Route::get('data', [StudentImportController::class, 'getData'])->name('students.import.data');
        Route::post('store', [StudentImportController::class, 'store'])->name('students.import.store');
        Route::get('edit/{studentImportId}', [StudentImportController::class, 'edit'])->name('students.import.edit');
        Route::put('update/{studentImportId}', [StudentImportController::class, 'update'])->name('students.import.update');
        Route::get('download/{id}', [StudentImportController::class, 'download'])->name('students.import.download');
        Route::delete('destroy/{studentImport}', [StudentImportController::class, 'destroy'])->name('students.import.destroy');

        // STUDENT IMPORT ROWS
        Route::prefix('rows')->group(function () {
            Route::get('/{studentImportId}', [StudentImportRowController::class, 'index'])->name('students.import.rows.index');
            Route::get('data/{studentImportId}', [StudentImportRowController::class, 'getData'])->name('students.import.rows.data');

            Route::post('store/{studentImportId}', [StudentImportRowController::class, 'store'])->name('students.import.rows.store');
            Route::get('edit/{studentImportRowId}', [StudentImportRowController::class, 'edit'])->name('students.import.rows.edit');
            Route::put('update/{studentImportRowId}', [StudentImportRowController::class, 'update'])->name('students.import.rows.update');
            Route::delete('destroy/{studentImportRowId}', [StudentImportRowController::class, 'destroy'])->name('students.import.rows.destroy');

            Route::post('import/{studentImportId}', [StudentImportRowController::class, 'import'])->name('students.import.rows.import');

            Route::post('commit-row/{studentImportRowId}', [StudentImportRowController::class, 'commitRow'])->name('students.import.rows.commitRow');
            Route::post('commit-all/{studentImportId}', [StudentImportRowController::class, 'commitAll'])->name('students.import.rows.commitAll');

            Route::post('uncommitAll/{studentImportRowId}', [StudentImportRowController::class, 'uncommitAll'])->name('students.import.rows.uncommitAll');
            Route::post('uncommit/{studentImportRowId}', [StudentImportRowController::class, 'unCommit'])->name('students.import.rows.uncommit');

            Route::get('errors/{studentImportRowId}', [StudentImportRowController::class, 'fetchErrors'])->name('students.import.rows.errors');
        });
    });
});

// Officer Routes
Route::prefix('officer')->middleware('auth')->name('officer.')->group(function () {
    // STUDENT ACADEMIC PROGRESS
    Route::get('students', [StudentAcademicProgressController::class, 'index'])->name('students');
    Route::get('data', [StudentAcademicProgressController::class, 'getData'])->name('students.data');
    Route::get('stats', [StudentAcademicProgressController::class, 'getStats'])->name('students.stats');

    Route::get('student-progress/{student_id}', [StudentAcademicProgressController::class, 'show'])->name('student.show');
    Route::get('student-progress/data/{student_id}', [StudentAcademicProgressController::class, 'getProgressData'])->name('student.progress.data');
    Route::get('student-progress/stats/{student_id}', [StudentAcademicProgressController::class, 'getProgressStats'])->name('student.progress.stats');
    Route::get('student-progress/pdf/{student_id}', [StudentAcademicProgressController::class, 'progressDownloadPdf'])->name('student.progress.pdf');
});


Route::prefix('student')->middleware('auth', StudentInformationCheck::class)->can('is-student')->group(function () {

    // ACADEMIC PROGRESS
    Route::get('academic-progress', [AcademicProgress::class, 'index'])->name('student.academic_progress.index');
    Route::get('academic-progress/data', [AcademicProgress::class, 'getData'])->name('student.academic_progress.data');
    Route::get('academic-progress/stats', [AcademicProgress::class, 'getStats'])->name('student.academic_progress.stats');
    Route::get('academic-progress/pdf', [AcademicProgress::class, 'downloadPDFView'])->name('student.academic_progress.pdf');

    // GRADE VIEWING
    Route::get('grades', [GradeController::class, 'index'])->name('student.grades.index');

    // STUDENT MANUAL
    Route::view('manual', 'app.student_portal.manual.index')->name('student.manual.index');

    // FAQs
    Route::view('faqs', 'app.student_portal.general_information.faqs')->name('student.faqs.index');
    // Help
    Route::view('help', 'app.student_portal.general_information.help')->name('student.help.index');
});
