<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LhpController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', fn() => 'Forgot Password Page')->name('password.request');
});

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Auth Routes
Route::middleware('auth')->group(function () {
    Route::get('/notifications/{notificationId}/read', [NotificationController::class, 'readAndRedirect'])
        ->name('notifications.read');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/lhp', [\App\Http\Controllers\LhpController::class, 'index'])->name('lhp.index');
    Route::get('/lhp/{lhp}', [\App\Http\Controllers\LhpController::class, 'show'])->name('lhp.show');
    Route::get('/temuan', [\App\Http\Controllers\FindingController::class, 'index'])->name('temuan.index');
    Route::get('/follow-up/{evidence}/file', [FollowUpController::class, 'download'])->name('followup.download');
    Route::put('/recommendation/{id}/status', [\App\Http\Controllers\FindingController::class, 'updateStatus'])->name('recommendation.status.update');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Admin Routes
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class.':admin')
        ->prefix('admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
    });

    // Evidence submission (accessible by SKPD from detail page)
    Route::post('/recommendation/{recommendation}/evidence', [FollowUpController::class, 'store'])->name('skpd.evidence.store');

    // SKPD specific Routes
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class.':skpd')
        ->prefix('skpd')->name('skpd.')->group(function () {
        // ... specific routes
    });

    // Auditor & Admin Routes
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class.':admin,auditor')
        ->prefix('auditor')->name('auditor.')->group(function () {
        // Full access to LHPs via Model Binding UUID
        Route::get('/lhp/create', [LhpController::class, 'create'])->name('lhp.create');
        Route::post('/lhp', [LhpController::class, 'store'])->name('lhp.store');
        Route::get('/lhp/{lhp}/preview', [LhpController::class, 'preview'])->name('lhp.preview');
        Route::patch('/lhp/{lhp}/submit-review', [LhpController::class, 'submitForReview'])->name('lhp.submit-review');
        Route::patch('/lhp/{lhp}/finalize', [LhpController::class, 'finalize'])->name('lhp.finalize');
        Route::get('/lhp/{lhp}/export', [LhpController::class, 'export'])->name('lhp.export');

        // Findings update
        Route::patch('/findings/{finding}', [FindingController::class, 'update'])->name('findings.update');
        
        // Follow-ups Verification
        Route::patch('/follow-up/{evidence}/verify', [FollowUpController::class, 'verify'])->name('followup.verify');
    });

    // Review Collaboration (Approve & Return)
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class.':admin,ketua_tim,inspektur_pembantu_1,inspektur_daerah')
        ->group(function () {
        Route::post('/lhp/{lhp}/review', [\App\Http\Controllers\ReviewController::class, 'store'])->name('lhp.review.store');
    });

    // Unpublish Route (Khusus Inspektur Daerah & Admin)
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class.':admin,inspektur_daerah')
        ->group(function () {
        Route::patch('/lhp/{lhp}/unpublish', [\App\Http\Controllers\ReviewController::class, 'unpublish'])->name('lhp.unpublish');
    });
});

// Fallback Route for 404 with Web Middleware (Persists Session)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
