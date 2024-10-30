<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function requestPasswordReset(Request $request)
    {
        // Validate email
        $request->validate(['email' => 'required|email']);

        // Check if the user exists
        $user = User::where('email', $request->email)->first();

        // If user not found, return a 404 response
        if (!$user) {
            return response()->json(['message' => 'User not found with email: ' . $request->email], 404);
        }

        // Generate a password reset token
        $token = Str::random(60);

        // Set expiration time (40 seconds from now)
        $expiresAt = Carbon::now()->addSeconds(40);

        // Store the token and expiration time in the password resets table
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => $expiresAt // Add expiration time
        ]);

        // Create the reset link
        $resetLink = url('http://localhost:5173/auth/update-password?token=' . $token);

        // Send reset email
        try {
            Mail::raw("To reset your password, please click the link below:\n$resetLink", function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Request');
            });
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send reset email.'], 500);
        }

        // Return a success response
        return response()->json(['message' => 'Reset link sent to your email.'], 200);
    }

    public function resetPassword(Request $request)
    {
        // Validate the request with email and password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'token' => 'required' // Add token validation
        ]);

        // Check if the token exists and has not expired
        $resetRecord = DB::table('password_resets')->where('token', $request->token)
            ->where('email', $request->email)
            ->where('expires_at', '>', Carbon::now()) // Check if the token is not expired
            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Invalid or Expired token, please request again!!'], 404);
        }

        // Check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update the user's password
        $user->password = bcrypt($request->password);
        $user->save();

        // Optionally, delete the reset record after successful password reset
        DB::table('password_resets')->where('email', $user->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully'], 200);
    }
}
