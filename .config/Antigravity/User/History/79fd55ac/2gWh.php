<?php

namespace App\Providers;

use App\Events\StudentAcademicProgressCreate;
use App\Events\StudentCheckProgress;
use App\Events\StudentCreationEvent;
use App\Events\StudentInformationCheck;
use App\Listeners\CreateStudentSubjectProgressRecords;
use App\Listeners\StudentCreationListener;
use App\Listeners\StudentInformationCheckUpdate;
use App\Listeners\UpdateAcademicProgress;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Event::listen(
            StudentCreationEvent::class,
            StudentCreationListener::class,
        );

        Event::listen(
            StudentAcademicProgressCreate::class,
            CreateStudentSubjectProgressRecords::class,
        );

        Event::listen(
            StudentCheckProgress::class,
            UpdateAcademicProgress::class,
        );

        // GATES
        Gate::define('is-admin', function (User $user) {
            return $user->user_type === 'ADMIN';
        });
        Gate::define('is-student', function (User $user) {
            return $user->user_type === 'STUDENT';
        });
        Gate::define('is-officer', function (User $user) {
            return $user->user_type === 'OFFICER';
        });
    }
}
