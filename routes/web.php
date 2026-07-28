<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Livewire\ExternalLogin;
use App\Livewire\ExternalDashboard;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

// Diagnostic Route to Test Database Connection on Vercel
Route::get('/test-db', function () {
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

// Temporary Route to Initialize Supabase Database directly on Vercel
Route::get('/deploy-init-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Database Supabase berhasil di-migrate & di-seed!',
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
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
