<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @deprecated Use UserLessonController::index() instead — this controller is unused.
 */
class CourseAppController extends Controller
{
    public function index()
    {
        return view('user.home.index');
    }
}
