<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use PDOException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'error' => $validator->errors()
            ], 400);
        }
        try {
            $user = User::create([
                'user_name' => $request->user_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            event(new Registered($user));

            Auth::login($user);
            return response()->json([
                'status' => 'User registered',
                'user' => $user,

            ]);
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
