<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VoteController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // User Routes
    Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
    Route::get('/polls/{id}/options', [PollController::class, 'getOptions']); // AJAX
    Route::post('/polls/{id}/vote', [VoteController::class, 'store']); // AJAX

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index'); 
        Route::post('/polls', [AdminController::class, 'store'])->name('admin.polls.store');
        
        // Module 4 Routes
        Route::get('/polls/{id}/votes', [AdminController::class, 'showVotes'])->name('admin.votes.show');
        Route::post('/votes/{id}/release', [AdminController::class, 'releaseIp']);
    });

});
