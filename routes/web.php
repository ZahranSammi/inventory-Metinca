<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/',function(){
    if(Auth::check()){
        return redirect()->route('dashboard');
    }
    return redirect()->route('home.main');
});

require __DIR__.'/auth.php';

// ============================================
// GUEST ROUTES (belum login)
// ============================================
Route::middleware('guest')->group(function () {
    
    // GET - Show Forms (Custom Views)
    Route::get('login', [AuthViewController::class, 'showLogin'])
        ->name('login');
    
    Route::get('register', [AuthViewController::class, 'showRegister'])
        ->name('register');
    
    Route::get('forgot-password', [AuthViewController::class, 'showForgotPassword'])
        ->name('password.request');
    
    Route::get('reset-password/{token}', [AuthViewController::class, 'showResetPassword'])
        ->name('password.reset');

    // POST - Handle Logic (Breeze Controllers)
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');
    
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
    
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');

    Route::prefix('home')->group(function(){

        Route::get('/',function(){
            return view('home.main');
        })->name('home.main');

        Route::get('/products',function(){
            return view('home.products');
        })->name('home.products');

        Route::get('/divisions',function(){
            return view('home.divisions');
        })->name('home.divisions');

        Route::get('/facilities',function(){
            return view('home.facilities');
        })->name('home.facilities');

        Route::get('/gallery',function(){
            return view('home.galleries');
        })->name('home.gallery');

    });
});

// ============================================
// Auth ROUTES (sudah login)
// ============================================


Route::middleware(['auth'])->group(function(){

    //dashboard
    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('dashboard');

    //machining process
    Route::prefix('machining')->name('machining.')->group(function(){

        //monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function(){

            Route::get('/',function(){
                return view('machining.monitoring.index');
            })->name('index');



        });

    });

});

// ============================================
// DEMO ROLE SWITCHER (For Testing 4 Roles)
// ============================================
Route::post('/demo/switch-role', [\App\Http\Controllers\Inventory\DemoRoleController::class, 'switchRole'])->name('demo.switch-role');

// ============================================
// INVENTORY / INVENTARIS ROUTES (MAZER TEMPLATE)
// ============================================
Route::prefix('inventory')->name('inventory.')->group(function(){
    // Dashboard (All Roles)
    Route::get('/', [\App\Http\Controllers\Inventory\DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [\App\Http\Controllers\Inventory\DashboardController::class, 'index'])->name('dashboard');

    // Data Barang (Semua Role bisa lihat, Staff Accounting bisa tambah/edit)
    Route::get('/barang', [\App\Http\Controllers\Inventory\BarangController::class, 'index'])->name('barang.index');
    Route::post('/barang', [\App\Http\Controllers\Inventory\BarangController::class, 'store'])->name('barang.store');
    Route::put('/barang/{id}', [\App\Http\Controllers\Inventory\BarangController::class, 'update'])->name('barang.update');

    // Khusus Staff Accounting: Pemeriksaan Ketersediaan & Kondisi Stok Fisik
    Route::get('/ketersediaan', [\App\Http\Controllers\Inventory\BarangController::class, 'ketersediaan'])->name('barang.ketersediaan');
    Route::post('/ketersediaan/{id}', [\App\Http\Controllers\Inventory\BarangController::class, 'updateKondisi'])->name('barang.ketersediaan.update');

    // Operasional Peminjaman (Semua Role sesuai hak akses)
    Route::get('/peminjaman', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'index'])->name('peminjaman.index');

    // Khusus Karyawan: Pengajuan, Tracking Pengajuan, Riwayat Pengajuan
    Route::get('/pengajuan/baru', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/pengajuan', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::get('/tracking-pengajuan', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'tracking'])->name('peminjaman.tracking');
    Route::get('/riwayat-pengajuan', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');

    // Khusus Manager: Verifikasi Peminjaman Barang
    Route::get('/verifikasi', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'verifikasiList'])->name('peminjaman.verifikasi');
    Route::post('/verifikasi/{id}', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'verifikasiAction'])->name('peminjaman.verifikasi.action');

    // Khusus Staff Accounting: Serah Terima Fisik & Konfirmasi Pengembalian
    Route::post('/peminjaman/{id}/serahkan', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'serahkan'])->name('peminjaman.serahkan');
    Route::post('/peminjaman/{id}/kembalikan', [\App\Http\Controllers\Inventory\PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');

    // Tracking Barang (Semua Role: Karyawan, Accounting, Manager, Direktur SDM)
    Route::get('/tracking', [\App\Http\Controllers\Inventory\TrackingController::class, 'index'])->name('tracking.index');

    // Khusus Manager: Laporan Barang Operasional
    Route::get('/laporan/operasional', [\App\Http\Controllers\Inventory\LaporanController::class, 'operasional'])->name('laporan.operasional');
    Route::post('/laporan/operasional', [\App\Http\Controllers\Inventory\LaporanController::class, 'store'])->name('laporan.store');

    // Khusus Direktur SDM: Pemeriksaan Laporan & Arsipkan Laporan
    Route::get('/laporan/pemeriksaan', [\App\Http\Controllers\Inventory\LaporanController::class, 'pemeriksaan'])->name('laporan.pemeriksaan');
    Route::post('/laporan/{id}/review', [\App\Http\Controllers\Inventory\LaporanController::class, 'review'])->name('laporan.review');

    // Profil Saya (Semua Role)
    Route::get('/profil', [\App\Http\Controllers\Inventory\ProfileController::class, 'index'])->name('profile');
    Route::post('/profil', [\App\Http\Controllers\Inventory\ProfileController::class, 'update'])->name('profile.update');
});


