<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VoteController;

// Auth Routes
Route::get('/', [AuthController::class, 'redirectHome']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
// Protected Routes (Auth check is inside Controllers)

// User Routes
Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
Route::get('/polls/{id}/options', [PollController::class, 'getOptions']); // AJAX
Route::post('/polls/{id}/vote', [VoteController::class, 'store']); // AJAX

// Admin Routes - Explicit paths because Mock Router ignores groups/prefixes
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index'); 
Route::post('/admin/polls', [AdminController::class, 'store'])->name('admin.polls.store');
Route::post('/admin/polls/{id}/toggle', [AdminController::class, 'toggleStatus'])->name('admin.polls.toggle');
Route::post('/admin/polls/{id}/delete', [AdminController::class, 'destroy'])->name('admin.polls.delete');
Route::get('/admin/reset', [AdminController::class, 'resetDatabase']); // Reset Route

// Module 4 Routes
Route::get('/admin/polls/{id}/votes', [AdminController::class, 'showVotes'])->name('admin.votes.show');
Route::post('/admin/votes/{id}/release', [AdminController::class, 'releaseIp']);
