<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // User exists, log them in
                // Mark email as verified since Google already verified it
                if (!$user->is_verified) {
                    $user->update([
                        'is_verified' => true,
                        'email_verified_at' => now(),
                    ]);
                }
            } else {
                // Create new user
                // Split name into first name and last name
                $googleName = $googleUser->name ?? $googleUser->nickname ?? null;
                $firstName = '';
                $lastName = '';
                
                if ($googleName) {
                    $nameParts = explode(' ', trim($googleName), 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';
                } else {
                    // Fallback to email prefix if no name
                    $emailParts = explode('@', $googleUser->email);
                    $firstName = $emailParts[0];
                    $lastName = '';
                }

                // Combine first_name and last_name into fname (full name)
                $fullName = trim($firstName . ' ' . $lastName);

                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'fname' => $fullName,
                    'email' => $googleUser->email,
                    'password' => Hash::make(Str::random(32)), // Random password since they use Google
                    'is_verified' => true, // Google already verified the email
                    'email_verified_at' => now(),
                ]);
            }

            // Regenerate session ID to prevent session fixation attacks
            // This must be done BEFORE authentication to ensure a new session is created
            request()->session()->regenerate();

            // Authenticate user using Laravel's session-based authentication
            Auth::login($user);

            // Store user_id in session for easy access
            request()->session()->put('user_id', $user->user_id);

            // Redirect to Nuxt frontend callback
            // Session cookie is automatically set and will be sent with subsequent requests
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            // Redirect to frontend callback - session is already established
            return redirect($frontendUrl . '/auth/callback');
        } catch (\Exception $e) {
            // Handle errors - redirect to frontend login with error
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect($frontendUrl . '/auth/login?error=google_auth_failed');
        }
    }
}
