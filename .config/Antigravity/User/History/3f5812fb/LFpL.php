<?php

namespace App\Http\Middleware;

use App\Events\StudentInformationCheck as StudentInformationCheckEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class StudentInformationCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type === 'STUDENT') {
            $student = auth()->user()->student;
           
            $year = now()->year;      // 2026
            $lastTwo = (int) substr($year, -2);

            $student_number = $student->student_number;

            $firstTwo = (int) substr($student_number, 0, 2);

            if ($lastTwo - $firstTwo > 5) {
                $student->year_level = 5;
            }
            else {
                $student->year_level = $lastTwo - $firstTwo;
            }
            
            $student->save();

            // Share whether the student has an email with all views
            $studentHasEmail = !empty(auth()->user()->email);
            View::share('studentHasEmail', $studentHasEmail);

            return $next($request);
        }

        return $next($request);
    }
}
