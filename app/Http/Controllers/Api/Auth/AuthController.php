<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class AuthController extends Controller
{
    use RespondsWithApi;

    private function otpKey(string $email, string $purpose): string
    {
        return "email_otp:{$purpose}:" . strtolower($email);
    }

    private function send(string $email, string $purpose): void
    {
        $otp = (string) random_int(100000, 999999);
        Cache::put($this->otpKey($email, $purpose), hash('sha256', $otp), now()->addMinutes(5));

        $subject = match ($purpose) {
            'register' => 'Verify Your Email Address',
            'login' => 'Your Login OTP',
            'reset' => 'Password Reset OTP',
            default => 'Your OTP Code',
        };

        Mail::raw("Your verification code is: {$otp}\n\nThis code will expire in 5 minutes.", function (Message $message) use ($email, $subject) {
            $message->to($email)
                ->subject($subject);
        });
    }

    public function register(Request $r)
    {
        $v = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|in:business,user'
        ]);

        $role = $v['role'] ?? 'user';
        unset($v['role']);
        $v['password'] = $v['password'] ?? str()->password(32);

        $u = User::create($v);
        $u->assignRole($role === 'business' ? 'Business Owner' : 'End User');

        if ($role === 'business') {
            $u->update(['is_business_owner' => true]);
        }

        $this->send($u->email, 'register');

        return $this->success(['email' => $u->email], __('messages.auth.otp_sent'), 201);
    }

    public function login(Request $r)
    {
        $v = $r->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'nullable|in:business,user'
        ]);

        $u = User::where('email', $v['email'])->first();

        if (! $u || ! Hash::check($v['password'], $u->password)) {
            return $this->error(__('messages.auth.invalid_credentials'), 400);
        }

        if (($v['role'] ?? 'user') === 'business' && ! $u->hasRole('Business Owner')) {
            return $this->error(__('messages.auth.business_owner_required'), 403);
        }

        // Send OTP for email verification
        $this->send($u->email, 'login');

        return $this->success(['email' => $u->email], __('messages.auth.otp_sent'), 201);
    }

    public function verifyOtp(Request $r)
    {
        $v = $r->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
            'purpose' => 'required|in:login,register,reset'
        ]);

        $key = $this->otpKey($v['email'], $v['purpose']);

        if (! hash_equals((string) Cache::get($key), hash('sha256', $v['code']))) {
            return $this->error(__('messages.auth.invalid_otp'), 422);
        }

        $u = User::where('email', $v['email'])->first();

        if (! $u) {
            return $this->error(__('messages.auth.account_not_found'), 404);
        }

        $u->update(['email_verified_at' => now()]);
        Cache::forget($key);

        if ($v['purpose'] === 'reset') {
            return $this->success(['reset_verified' => true], __('messages.auth.reset_verified'));
        }

        return $this->success([
            'token' => $u->createToken('mobile')->plainTextToken,
            'user' => new UserResource($u->load('roles'))
        ], __('messages.auth.otp_verified'));
    }

    public function resendOtp(Request $r)
    {
        $v = $r->validate([
            'email' => 'required|email',
            'purpose' => 'required|in:login,register,reset'
        ]);

        $this->send($v['email'], $v['purpose']);

        return $this->success(['email' => $v['email']], __('messages.auth.otp_resent'), 201);
    }

    public function resetPassword(Request $r)
    {
        $v = $r->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
            'password' => 'required|confirmed|min:8'
        ]);

        if (! hash_equals((string) Cache::get($this->otpKey($v['email'], 'reset')), hash('sha256', $v['code']))) {
            return $this->error(__('messages.auth.invalid_otp'), 422);
        }

        $u = User::where('email', $v['email'])->firstOrFail();
        $u->forceFill(['password' => $v['password']])->save();
        $u->tokens()->delete();
        Cache::forget($this->otpKey($v['email'], 'reset'));

        return $this->success(null, __('messages.auth.password_reset'));
    }

    public function forgotPassword(Request $r)
    {
        $v = $r->validate(['email' => 'required|email']);

        $u = User::where('email', $v['email'])->first();

        if (! $u) {
            return $this->success(null, __('messages.auth.if_account_exists'));
        }

        $this->send($u->email, 'reset');

        return $this->success(['email' => $u->email], __('messages.auth.otp_sent'), 201);
    }

    public function changePassword(Request $r)
    {
        $v = $r->validate([
            'current_password' => 'required|string',
            'password' => 'required|confirmed|min:8'
        ]);

        $u = $r->user();

        if (! Hash::check($v['current_password'], $u->password)) {
            return $this->error(__('messages.auth.current_password_incorrect'), 422);
        }

        $u->update(['password' => $v['password']]);

        return $this->success(null, __('messages.auth.password_changed'));
    }

    public function toggleRole(Request $r)
    {
        $u = $r->user();

        if (! $u->is_business_owner || ! $u->hasRole('Business Owner')) {
            return $this->error(__('messages.auth.business_owner_required'), 403);
        }

        $u->update(['active_mode' => $u->active_mode === 'user' ? 'business' : 'user']);

        return $this->success(new UserResource($u->load('roles')));
    }

    public function me(Request $r)
    {
        return $this->success(new UserResource($r->user()->load('roles')));
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();

        return $this->success(null, __('messages.auth.logged_out'));
    }
}
