<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // Split the full name into first and last name
                $nameParts = explode(' ', $googleUser->getName(), 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                $user = User::create([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $googleUser->getEmail(),
                    'google_id'  => $googleUser->getId(),
                    'user_type'  => 'USER',
                    'password'   => Hash::make(Str::random(16)),
                ]);
            }

            Auth::login($user);

            if ($user->user_type === 'ADMIN') {
                return redirect()->route('admin.home');
            }

            return redirect()->route('user.home');
        } catch (Exception $e) {
            return redirect()->route('auth.sign-in')
                ->with('error', 'Failed to login with Google.');
        }
    }
}
