<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Livewire\ExternalLogin;
use App\Livewire\ExternalDashboard;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

// Serve temporary export files from /tmp (needed on Vercel's read-only filesystem)
Route::get('/export-tmp', function () {
    $file = request('file');

    // Security: only allow safe filenames (no path traversal)
    if (!$file || !preg_match('/^[a-zA-Z0-9_\-]+\.xlsx$/', $file)) {
        abort(404);
    }

    $path = '/tmp/' . $file;

    if (!file_exists($path)) {
        abort(404, 'Export file not found or expired.');
    }

    return response()->download($path, $file, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend(true);
});

// Protected Diagnostic Route to Test Database Connection on Vercel
Route::get('/test-db', function () {
    if (request('secret') !== env('DEPLOY_SECRET_KEY', 'proman2026secret')) {
        abort(403, 'Unauthorized access to diagnostic route.');
    }

    try {
        $pdo = DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        $database = DB::connection()->getDatabaseName();
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        $userCount = DB::table('users')->count();

        return response()->json([
            'status' => 'connected',
            'driver' => $driver,
            'database' => $database,
            'tables_count' => count($tables),
            'user_count' => $userCount,
            'tables' => array_column($tables, 'table_name'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error_message' => $e->getMessage(),
            'db_connection' => env('DB_CONNECTION'),
            'db_host' => env('DB_HOST'),
            'db_port' => env('DB_PORT'),
            'db_database' => env('DB_DATABASE'),
            'db_username' => env('DB_USERNAME'),
            'has_db_url' => !empty(env('DB_URL')),
        ], 500);
    }
});

// Protected Route to Initialize Supabase Database & Super Admin Role directly on Vercel
Route::get('/deploy-init-db', function () {
    if (request('secret') !== env('DEPLOY_SECRET_KEY', 'proman2026secret')) {
        abort(403, 'Unauthorized access to database initialization route.');
    }

    try {
        // Run migration gracefully (catch duplicate table errors if created via SQL editor)
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $mError) {
            // Ignore duplicate table error
        }

        // Run seeders
        try {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $sError) {
            // Ignore duplicate seed error
        }

        // Explicitly ensure user adityacahyo104@gmail.com has super_admin role
        $user = \App\Models\User::where('email', 'adityacahyo104@gmail.com')->first();
        if ($user) {
            $user->assignRole('super_admin');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Super Admin adityacahyo104@gmail.com berhasil diaktifkan!',
            'user' => $user ? $user->only(['id', 'name', 'email']) : null,
            'roles' => $user ? $user->roles->pluck('name') : [],
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// Google Authentication Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// External Dashboard Routes
Route::prefix('external')->name('external.')->group(function () {
    Route::get('/{token}', ExternalLogin::class)->name('login');
    Route::get('/{token}/dashboard', ExternalDashboard::class)->name('dashboard');
});
