<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Validate Request
            $request->validate([
                'email' => 'required|email',
                'pasword' => 'required'
            ]);

            // Find User By Email
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Unauthorized', 401);
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception("Invalid password");
            }

            // Generate Token
            $tokenResult = $user->createToken('authoToken')->plainTextToken;

            // Return Response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login Success');
        } catch (Exception $error) {
            return ResponseFormatter::error('Authentication Failed');
        }
    }

    public function register(Request $request)
    {
        try {
            // Validate Request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password]
            ]);

            // Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'passowrd' => Hash::make($request->password),
            ]);

            // Generate Token
            $tokenResult = $user->createToken('authToken')->plainTextTokrn;

            // Return Response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Register Success');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage());
        }
    }
}
