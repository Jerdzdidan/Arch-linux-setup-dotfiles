<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    /**
     * Update the authenticated student's email address.
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],
        ]);

        $user = auth()->user();
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email address saved successfully.',
        ]);
    }
}
