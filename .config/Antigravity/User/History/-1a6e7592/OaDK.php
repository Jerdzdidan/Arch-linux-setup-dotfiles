<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserProgressController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\User\CourseAppController;
use App\Http\Controllers\User\SimulationController;
use App\Http\Controllers\User\UserLessonController;
use App\Http\Controllers\User\UserQuizController;
use App\Http\Controllers\User\CertificateController;
use Illuminate\Support\Facades\Route;

// LANDING PAGE
Route::get('', [AuthController::class, 'index'])->name('landing.index');

// AUTH ROUTES
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google');
    Route::get('google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

    Route::get('sign-in', [AuthController::class, 'signin'])->name('sign-in');
    Route::post('login', [AuthController::class, 'authenticate'])->name('authenticate');

    Route::get('sign-up', [AuthController::class, 'signup'])->name('sign-up');
    Route::post('create-user', [AuthController::class, 'store'])->name('create-user');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// MAIN USER ROUTES
Route::prefix('')->middleware('auth')->group(function () {
    Route::get('home', [UserLessonController::class, 'index'])->name('user.home');

    // Certificate Routes
    Route::prefix('certificate')->name('certificate.')->group(function () {
        Route::get('check', [CertificateController::class, 'checkEligibility'])->name('check');
        Route::get('view', [CertificateController::class, 'view'])->name('view');
        Route::get('download', [CertificateController::class, 'download'])->name('download');
        Route::get('generate', [CertificateController::class, 'generate'])->name('generate');
    });

    Route::prefix('lessons')->name('lessons.')->group(function () {
        Route::get('show/{id}', [UserLessonController::class, 'show'])->name('show');

        Route::prefix('quiz')->name('quiz.')->group(function () {
            Route::get('{id}', [UserQuizController::class, 'show'])->name('show');
            Route::post('submit/{id}', [UserQuizController::class, 'submit'])->name('submit');
            Route::get('results/{id}/{attempt}', [UserQuizController::class, 'results'])->name('results');
            Route::get('retake/{id}', [UserQuizController::class, 'retake'])->name('retake');
        });

        Route::prefix('simulations')->name('simulations.')->group(function () {
            Route::get('{id}', [SimulationController::class, 'index'])->name('index');
            Route::get('{id}/{simId}', [SimulationController::class, 'show'])->name('show');
            Route::post('{id}/{simId}/start', [SimulationController::class, 'start'])->name('start');
            Route::post('{id}/{simId}/submit', [SimulationController::class, 'submit'])->name('submit');
            Route::get('{id}/{simId}/results/{attempt}', [SimulationController::class, 'results'])->name('results');
            Route::get('{id}/{simId}/retake', [SimulationController::class, 'retake'])->name('retake');
        });
    });
});

// ADMIN ROUTES
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('home', function () {
        return view('admin.home.index');
    })->name('home');

    // LESSONS
    Route::prefix('lessons')->name('lessons.')->group(function () {
        Route::get('', [LessonController::class, 'index'])->name('index');

        Route::post('store', [LessonController::class, 'store'])->name('store');

        Route::get('edit/{id}', [LessonController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [LessonController::class, 'update'])->name('update');

        Route::get('destroy/{id}', [LessonController::class, 'destroy'])->name('destroy');

        Route::prefix('quiz')->name('quiz.')->group(function () {
            Route::get('{id}', [QuizController::class, 'show'])->name('show');
            Route::post('store/{id}', [QuizController::class, 'store'])->name('store');
        });

        Route::prefix('question')->name('question.')->group(function () {
            Route::post('store/{id}', [QuizController::class, 'storeQuestion'])->name('store');
            Route::get('edit/{id}', [QuizController::class, 'editQuestion'])->name('edit');
            Route::put('update/{id}', [QuizController::class, 'updateQuestion'])->name('update');
            Route::delete('destroy/{id}', [QuizController::class, 'destroyQuestion'])->name('destroy');
            Route::post('re-order', [QuizController::class, 'reorderQuestions'])->name('reorder');
        });
    });

    // USER MANAGEMENT
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('index');

        Route::get('data', [UserController::class, 'getData'])->name('data');
        Route::get('stats', [UserController::class, 'getStats'])->name('stats');

        Route::post('store', [UserController::class, 'store'])->name('store');

        Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [UserController::class, 'update'])->name('update');

        Route::delete('destroy/{id}', [UserController::class, 'destroy'])->name('destroy');

        Route::post('toggle/{id}', [UserController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('overview', [AnalyticsController::class, 'overview'])->name('overview');
        Route::get('quiz', [AnalyticsController::class, 'quizAnalytics'])->name('quiz');
        Route::get('simulation', [AnalyticsController::class, 'simulationAnalytics'])->name('simulation');
        Route::get('heatmap', [AnalyticsController::class, 'heatmap'])->name('heatmap');
        Route::get('export', [AnalyticsController::class, 'export'])->name('export');
    });

    // USER PROGRESS
    Route::prefix('user-progress')->name('user-progress.')->group(function () {
        Route::get('', [UserProgressController::class, 'index'])->name('index');
        Route::get('data', [UserProgressController::class, 'getData'])->name('data');
        Route::get('show/{id}', [UserProgressController::class, 'show'])->name('show');
    });
});
