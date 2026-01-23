<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerifyEmailOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Support both your current frontend payload (email/password/fname)
        // and the more complete payload (first_name/last_name).
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'fname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
        ])));
        $fname = $fullName !== '' ? $fullName : ($validated['fname'] ?? Str::before($validated['email'], '@'));

        $user = User::create([
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'fname' => $fname,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => null,
            'email_verification_token' => null,
            'is_verified' => false,
        ]);

        // Note: Session is NOT created during registration
        // User must verify email first, then login to establish a session
        // This ensures sessions are only created after successful authentication

        // Send OTP for email verification (cache-backed, expires in 2 minutes).
        $this->sendEmailVerificationOtp($user, force: true);

        // For API routes, always return JSON
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Registration successful. Please verify your email.',
                'redirect_url' => '/verify-email',
            ], 201);
        }

        // For web requests, redirect to verify page
        return redirect('/verify-email');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Regenerate session ID to prevent session fixation attacks
        // This must be done BEFORE authentication to ensure a new session is created
        $request->session()->regenerate();

        // Authenticate user using Laravel's session-based authentication
        Auth::login($user);

        // Store user_id in session for easy access
        $request->session()->put('user_id', $user->user_id);

        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
            ]);
        }

        // For web requests, redirect to tasks page
        return redirect()->route('tasks');
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'Email is already verified.'], 200);
        }

        $cacheKey = $this->otpCacheKey($user->user_id);
        $expectedHash = Cache::get($cacheKey);

        if (! $expectedHash) {
            return response()->json([
                'message' => 'Your code has expired. Please resend a new code.',
                'code' => 'OTP_EXPIRED',
            ], 422);
        }

        $providedHash = $this->hashOtp($user->user_id, $request->string('code'));

        if (! hash_equals($expectedHash, $providedHash)) {
            return response()->json([
                'message' => 'Invalid verification code.',
                'code' => 'OTP_INVALID',
            ], 422);
        }

        // Invalidate code after use
        Cache::forget($cacheKey);
        Cache::forget($this->otpSentAtCacheKey($user->user_id));

        $user->forceFill([
            'is_verified' => true,
            'email_verified_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Email verified successfully.',
            'redirect_url' => '/verification-success',
        ]);
    }

    public function resendEmailOtp(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'Email is already verified.'], 200);
        }

        // Anti-spam: only allow resend after the previous OTP expires (2 minutes)
        $sentAtKey = $this->otpSentAtCacheKey($user->user_id);
        $sentAt = Cache::get($sentAtKey);
        if ($sentAt) {
            $elapsed = (int) now()->diffInSeconds(\Carbon\Carbon::parse($sentAt));
            $remaining = max(0, 120 - $elapsed);
            if ($remaining > 0) {
                return response()->json([
                    'message' => "Please wait {$this->formatTimeRemaining($remaining)} before requesting a new code.",
                    'code' => 'OTP_RESEND_TOO_SOON',
                    'retry_after_seconds' => $remaining,
                ], 429);
            }
        }

        $this->sendEmailVerificationOtp($user, force: true);

        return response()->json([
            'message' => 'A new verification code has been sent.',
            'expires_in_seconds' => 120,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        // Get user before logout (for API responses)
        $user = $request->user();

        // Destroy the session
        Auth::logout();

        // Invalidate the session cookie
        $request->session()->invalidate();
        
        // Regenerate CSRF token for security
        $request->session()->regenerateToken();

        // Clear the session cookie by setting it to expire in the past
        $cookieName = config('session.cookie');
        $cookie = cookie($cookieName, '', -1, config('session.path'), config('session.domain'), config('session.secure'), config('session.http_only'), false, config('session.same_site'));

        if ($request->is('api/*')) {
            return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
        }

        return redirect('/auth/login')->withCookie($cookie);
    }

    private function sendEmailVerificationOtp(User $user, bool $force = false): void
    {
        $cacheKey = $this->otpCacheKey($user->user_id);

        // If an OTP exists and we aren't forcing, don't resend.
        if (! $force && Cache::has($cacheKey)) {
            return;
        }

        $otp = (string) random_int(0, 999999);
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);

        Cache::put($cacheKey, $this->hashOtp($user->user_id, $otp), now()->addMinutes(2));
        Cache::put($this->otpSentAtCacheKey($user->user_id), now()->toIso8601String(), now()->addMinutes(2));

        Mail::to($user->email)->send(new VerifyEmailOtpMail($user, $otp));
    }

    private function otpCacheKey(int|string $userId): string
    {
        return "email_otp:{$userId}";
    }

    private function otpSentAtCacheKey(int|string $userId): string
    {
        return "email_otp_sent_at:{$userId}";
    }

    private function hashOtp(int|string $userId, string|\Stringable $otp): string
    {
        // Hash OTP so the raw code is not stored anywhere server-side.
        $secret = (string) config('app.key');
        return hash_hmac('sha256', "{$userId}|{$otp}", $secret);
    }

    /**
     * Send OTP for registration without creating user account.
     * User account will only be created after OTP verification.
     */
    public function sendRegistrationOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        // Check if email already exists
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'An account with this email already exists.',
            ], 422);
        }

        // Use email as unique identifier for pending registration
        $pendingKey = "pending_registration:{$validated['email']}";
        
        // Anti-spam: only allow resend after the previous OTP expires (2 minutes)
        $sentAtKey = "pending_registration_sent_at:{$validated['email']}";
        $sentAt = Cache::get($sentAtKey);
        if ($sentAt) {
            $elapsed = (int) now()->diffInSeconds(\Carbon\Carbon::parse($sentAt));
            $remaining = max(0, 120 - $elapsed);
            if ($remaining > 0) {
                return response()->json([
                    'message' => "Please wait {$this->formatTimeRemaining($remaining)} before requesting a new code.",
                    'code' => 'OTP_RESEND_TOO_SOON',
                    'retry_after_seconds' => $remaining,
                ], 429);
            }
        }

        // Generate and store OTP
        $otp = (string) random_int(0, 999999);
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);

        $otpCacheKey = "pending_otp:{$validated['email']}";
        Cache::put($otpCacheKey, $this->hashPendingOtp($validated['email'], $otp), now()->addMinutes(2));
        Cache::put($sentAtKey, now()->toIso8601String(), now()->addMinutes(2));

        // Create a temporary user object for email (not saved to DB)
        $tempUser = new User([
            'email' => $validated['email'],
            'fname' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')) ?: 'User',
        ]);

        // Send OTP email
        Mail::to($validated['email'])->send(new VerifyEmailOtpMail($tempUser, $otp));

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }

    /**
     * Resend OTP for registration.
     */
    public function resendRegistrationOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        // Check if email already exists
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'An account with this email already exists.',
            ], 422);
        }

        // Anti-spam check
        $sentAtKey = "pending_registration_sent_at:{$validated['email']}";
        $sentAt = Cache::get($sentAtKey);
        if ($sentAt) {
            $elapsed = (int) now()->diffInSeconds(\Carbon\Carbon::parse($sentAt));
            $remaining = max(0, 120 - $elapsed);
            if ($remaining > 0) {
                return response()->json([
                    'message' => "Please wait {$this->formatTimeRemaining($remaining)} before requesting a new code.",
                    'code' => 'OTP_RESEND_TOO_SOON',
                    'retry_after_seconds' => $remaining,
                ], 429);
            }
        }

        // Generate and store new OTP
        $otp = (string) random_int(0, 999999);
        $otp = str_pad($otp, 6, '0', STR_PAD_LEFT);

        $otpCacheKey = "pending_otp:{$validated['email']}";
        Cache::put($otpCacheKey, $this->hashPendingOtp($validated['email'], $otp), now()->addMinutes(2));
        Cache::put($sentAtKey, now()->toIso8601String(), now()->addMinutes(2));

        // Create a temporary user object for email
        $tempUser = new User([
            'email' => $validated['email'],
            'fname' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')) ?: 'User',
        ]);

        // Send OTP email
        Mail::to($validated['email'])->send(new VerifyEmailOtpMail($tempUser, $otp));

        return response()->json([
            'message' => 'A new verification code has been sent.',
            'expires_in_seconds' => 120,
        ]);
    }

    /**
     * Verify OTP and create user account.
     */
    public function verifyAndRegister(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        // Check if email already exists
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'An account with this email already exists.',
            ], 422);
        }

        // Verify OTP
        $otpCacheKey = "pending_otp:{$validated['email']}";
        $expectedHash = Cache::get($otpCacheKey);

        if (! $expectedHash) {
            return response()->json([
                'message' => 'Your code has expired. Please request a new code.',
                'code' => 'OTP_EXPIRED',
            ], 422);
        }

        $providedHash = $this->hashPendingOtp($validated['email'], $request->string('code'));

        if (! hash_equals($expectedHash, $providedHash)) {
            return response()->json([
                'message' => 'Invalid verification code.',
                'code' => 'OTP_INVALID',
            ], 422);
        }

        // OTP is valid - clear cache and create user
        Cache::forget($otpCacheKey);
        Cache::forget("pending_registration_sent_at:{$validated['email']}");

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
        ])));
        $fname = $fullName !== '' ? $fullName : Str::before($validated['email'], '@');

        $user = User::create([
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'fname' => $fname,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'is_verified' => true,
        ]);

        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // Authenticate user using Laravel's session-based authentication
        Auth::login($user);

        // Store user_id in session for easy access
        $request->session()->put('user_id', $user->user_id);

        return response()->json([
            'message' => 'Account created and email verified successfully.',
            'user' => $user,
        ], 201);
    }

    private function hashPendingOtp(string $email, string|\Stringable $otp): string
    {
        // Hash OTP for pending registration (no user_id yet)
        $secret = (string) config('app.key');
        return hash_hmac('sha256', "{$email}|{$otp}", $secret);
    }

    /**
     * Format seconds into human-readable time (e.g., "1 minute and 30 seconds")
     */
    private function formatTimeRemaining(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0 seconds';
        }

        $minutes = (int) floor($seconds / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes');
        }
        if ($secs > 0) {
            $parts[] = $secs . ' ' . ($secs === 1 ? 'second' : 'seconds');
        }

        return implode(' and ', $parts);
    }
}


