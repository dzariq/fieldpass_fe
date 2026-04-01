<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\AdminsController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\Auth\ForgotPasswordController;
use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Controllers\Backend\Association\AssociationController;
use App\Http\Controllers\Backend\Competition\CompetitionController;
use App\Http\Controllers\Backend\Club\ClubController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\Auth\RegisterController;
use App\Http\Controllers\Backend\RolesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', 'HomeController@redirectAdmin')->name('index');
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
Route::get('/home', 'HomeController@index')->name('home');

/**
 * Admin routes
 */
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('roles', RolesController::class);
    Route::resource('admins', AdminsController::class);
    Route::resource('associations', AssociationController::class);
    Route::resource('competitions', CompetitionController::class);
    Route::resource('clubs', ClubController::class);

    // Login Routes.
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login/submit', [LoginController::class, 'login'])->name('login.submit');

    // Logout Routes.
    Route::post('/logout/submit', [LoginController::class, 'logout'])->name('logout.submit');

    //competition invites
    Route::get('/competition/invites', [CompetitionController::class, 'invites'])->name('competition.invites.index');
    Route::get('/notification', [NotificationController::class, 'index'])->name('notifications');

    // Forget Password Routes.
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/reset/submit', [ForgotPasswordController::class, 'reset'])->name('password.update');
})->middleware('auth:admin');
