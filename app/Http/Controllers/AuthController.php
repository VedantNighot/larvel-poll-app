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
        // Simplified Login
        Auth::loginUsingId(1);
        return redirect()->route('polls.index');
    }
    
    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
