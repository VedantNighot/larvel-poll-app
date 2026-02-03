<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login'); // login.blade.php
    }

    public function login(Request $request)
    {
        // Strict Credential Check for Deployment
        $email = $request->email;
        $password = $request->password;

        if ($email === 'admin@example.com' && $password === 'admin') {
            Auth::loginUsingId(1); // Log in as Admin 1
            return redirect()->route('polls.index');
        }

        return redirect()->back()->with('error', 'Invalid Email or Password');
    }
    
    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
