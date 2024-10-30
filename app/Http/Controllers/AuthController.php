<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'role_id' => 'required|integer',
            'status' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if (User::where('email', $validatedData['email'])->exists()) {
            return response()->json(['message' => 'Email already exists'], 409);
        }

        $validatedData['password'] = Hash::make($validatedData['password']); // Hash the password

        try {
            $user = User::create($validatedData);
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User creation failed', 'error' => $e->getMessage()], 500);
        }
    }

public function login(Request $request)
{
    \Log::info('Login request data: ', $request->all());

    $credentials = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string|min:8',
    ]);

    // Find user by email
    $user = User::where('email', $credentials['email'])->first();

    // Check for user existence and password validity
    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        \Log::info('Invalid credentials for email: ' . $credentials['email']);
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Check if the account is active
    if ($user->status !== 'is_active') {
        \Log::info('Inactive account for email: ' . $credentials['email']);
        return response()->json(['message' => 'Account is not active'], 403);
    }

    try {
        // Create token with an expiration time of 8 hours
        $token = $user->createToken('authToken', [], Carbon::now()->addHours(8))->plainTextToken;

        // Log the login activity to the audit_trail table
        \DB::table('audit_trail')->insert([
            'user_id' => $user->user_id,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'action' => 'login',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Store token in local storage on the client-side
        \Log::info('Login successful for email: ' . $credentials['email']);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'role_id' => $user->role_id
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Error during login: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    }
}




    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $cookie = cookie()->forget('auth_token');

        return response()->json(['message' => 'Logged out successfully'])->cookie($cookie);
    }

    public function getLoggedUserProfile(Request $request)
{
    $user = $request->user()->load('role'); // Eager load the role relationship

    return response()->json([
        'user_id' => $user->user_id,
        'email' => $user->email,
        'name' => $user->name,
        'role_id' => $user->role_id,
        'category' => $user->role->category, // Include the role category
        'status' => $user->status,
    ]);
}

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if ($request->email !== $user->email) {
            return response()->json(['message' => 'Email does not match the logged-in user.'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }

    public function getLoggedUserName(Request $request)
    {
        $user = $request->user();

        return response()->json(['email' => $user->email]);
    }

    public function getLoggedUserID(Request $request)
    {
        $user = $request->user();

        return response()->json(['user_id' => $user->user_id]);
    }


public function users(Request $request)
{
    try {
        // Eager load the 'role' relationship and return the category instead of role_id
        $users = User::with('role') // Load the related Role model
                     ->orderBy('user_id', 'desc')
                     ->get()
                     ->map(function ($user) {
                         return [
                             'user_id' => $user->user_id,
                             'name' => $user->name,
                             'email' => $user->email,
                             'status' => $user->status,
                             'role' => optional($user->role)->category, // Safely access category with optional
                         ];
                     });

        // Return the response with HTTP status 200
        return response()->json(['users' => $users], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error fetching users: ' . $e->getMessage());

        // Return a JSON error response with HTTP status 500
        return response()->json(['error' => 'Failed to fetch users.'], 500);
    }
}



  // Show user by ID
public function showUserById($user_id)
{
    $user = User::with('role')->find($user_id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json([
        'user_id' => $user->user_id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => $user->status,
        'role' => optional($user->role)->category,
    ], 200);
}

// Update user by ID
public function updateUser(Request $request, $user_id)
{
    $user = User::find($user_id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'role_id' => 'required|integer',
        'status' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'password' => 'nullable|string|min:8',
    ]);

    if ($request->filled('password')) {
        $validatedData['password'] = Hash::make($validatedData['password']);
    } else {
        unset($validatedData['password']);
    }

    $user->update($validatedData);

    return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
}




    // Delete user
    public function deleteUser($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function logUserActivity(Request $request)
    {
        try {
            // Log the activity for debugging purposes
            \Log::info("Logging activity for user_id: {$request->user_id}, action: {$request->action}");

            // Create a new log entry
            UserLog::create([
                'user_id' => $request->user_id,
                'role_id' => Auth::user()->role_id,
                'action' => $request->action,
            ]);

            return response()->json(['message' => 'Activity logged successfully'], 200);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to log user activity: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to log activity'], 500);
        }
    }

public function getAuditTrail(Request $request)
{
    try {
        // Fetch the audit trail records for the logged-in user, joining with the roles table
        $auditTrail = \DB::table('audit_trail')
            ->join('roles', 'audit_trail.role_id', '=', 'roles.role_id') // Join roles table
            ->where('audit_trail.user_id', $request->user()->user_id) // Fetch records for the logged-in user
            ->select('audit_trail.*', 'roles.category') // Select all fields from audit_trail and the category from roles
            ->orderBy('audit_trail.created_at', 'desc') // Order by created_at in descending order
            ->get();

        return response()->json(['audit_trail' => $auditTrail], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error fetching audit trail: ' . $e->getMessage());

        // Return a JSON error response with HTTP status 500
        return response()->json(['error' => 'Failed to fetch audit trail.'], 500);
    }
}



}














