<?php

use App\Http\Controllers\Backend\AjaxController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\AdminsController;
use App\Http\Controllers\Backend\PlayersController;
use App\Http\Controllers\Backend\MatchUpdateController;
use App\Http\Controllers\Backend\Training\TrainingController;
use App\Http\Controllers\Backend\PlayerLineupController;
use App\Http\Controllers\Backend\FantasyController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\Auth\ForgotPasswordController;
use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Controllers\Backend\Association\AssociationController;
use App\Http\Controllers\Backend\Competition\CompetitionController;
use App\Http\Controllers\Backend\Match\MatchesController;
use App\Http\Controllers\Backend\Club\ClubController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DemoDataController;
use App\Http\Controllers\Backend\Auth\RegisterController;
use App\Http\Controllers\Backend\RolesController;

use App\Http\Controllers\PlayerBackend\PlayerDashboardController;
use App\Http\Controllers\PlayerBackend\Auth\PlayerRegisterController;
use App\Http\Controllers\PlayerBackend\Auth\PlayerForgotPasswordController;
use App\Http\Controllers\PlayerBackend\Auth\PlayerLoginController;


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

Route::get('/player_register', [PlayerRegisterController::class, 'index'])->name('playerregister');
Route::post('/playerregister', [PlayerRegisterController::class, 'register'])->name('playerregister.submit');
Route::get('/home', 'HomeController@index')->name('home');

/**
 * Admin routes
 */
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {

  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
  Route::post('/demo-data/enable', [DemoDataController::class, 'enable'])->name('demo.enable');
  Route::post('/demo-data/disable', [DemoDataController::class, 'disable'])->name('demo.disable');
  Route::resource('roles', RolesController::class);
  Route::resource('admins', AdminsController::class);
  Route::resource('players', PlayersController::class);
  Route::resource('associations', AssociationController::class);
  Route::resource('competitions', CompetitionController::class);

  Route::post('/competition/invites/approve-payment', [CompetitionController::class, 'approveWithPayment'])->name('competition.invites.approve.payment');

  // In your admin routes group
  Route::delete('/fantasy/{competition}/matchweek/{matchweek}', [FantasyController::class, 'deleteMatchweek'])
    ->name('fantasy.delete-matchweek');

  Route::get('/fantasy/points', [FantasyController::class, 'points'])
    ->name('fantasy.points');

  Route::put('/fantasy/points/{competitionId}/update', [FantasyController::class, 'points_update'])
    ->name('fantasy.points.update');

  Route::get('/fantasy/points/{id}/edit', [FantasyController::class, 'points_edit'])
    ->name('fantasy.points.edit');
    

  Route::get('/fantasy/points/new', [FantasyController::class, 'points_new'])
    ->name('fantasy.points.new');

  Route::post('/fantasy/points/create', [FantasyController::class, 'points_store'])
    ->name('fantasy.points.store');

  Route::post('/admins/{admin}/reinvite', [AdminsController::class, 'reinvite'])->name('admins.reinvite');
  // In your routes file (web.php or admin.php)
  Route::patch('fantasy/{competition}/matchweek/{matchweek}/update-status', [FantasyController::class, 'updateStatus'])
    ->name('fantasy.update-status');

  Route::post('/players/{player}/reinvite', [PlayersController::class, 'reinvite'])->name('players.reinvite');
  Route::get('/bulk-upload', [PlayersController::class, 'bulkUploadForm'])->name('players.bulk.form');
  Route::post('/bulk-upload', [PlayersController::class, 'bulkUploadStore'])->name('players.bulk.store');
  Route::get('/bulk-upload/template', [PlayersController::class, 'downloadTemplate'])->name('players.bulk.template');
  Route::get('/admin/players/list', [PlayersController::class, 'playersList'])->name('players.list');
  Route::post('/players/{id}/update-market-value', [PlayersController::class, 'updateMarketValue'])->name('players.update.market.value');

  Route::resource('fantasy', FantasyController::class);
  Route::resource('clubs', ClubController::class);
  Route::resource('matches', MatchesController::class);

  Route::get('/invite', [PlayersController::class, 'inviteForm'])->name('players.invite');
  Route::post('/invite/search', [PlayersController::class, 'searchPlayer'])->name('players.invite.search');
  Route::post('/invite/send', [PlayersController::class, 'sendInvite'])->name('players.invite.send');

  // Login Routes.
  Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
  Route::post('/login/submit', [LoginController::class, 'login'])->name('login.submit');

  // Logout Routes.
  Route::post('/logout/submit', [LoginController::class, 'logout'])->name('logout.submit');

  Route::get('/match/checkin', [MatchesController::class, 'checkin'])->name('match.checkin');
  Route::get('/match/checkin_list', [MatchesController::class, 'checkin_list'])->name('match.checkin_list');
  Route::post('/match/checkin_verify', [MatchesController::class, 'checkinVerify'])->name('match.checkin_verify');
  Route::post('/match/event', [MatchUpdateController::class, 'event_save'])->name('match.event_save');
  Route::get('/match/event', [MatchUpdateController::class, 'match_info'])->name('match.match_info');
  Route::post('/match/delete-event', [MatchUpdateController::class, 'event_delete'])->name('match.deleteEvent');

  //competition invites
  Route::get('/competition/invites', [CompetitionController::class, 'invites'])->name('competition.invites.index');
  Route::get('/competition/approve/{id}', [CompetitionController::class, 'approve'])->name('competition.invites.approve');
  Route::get('/competition/reject/{id}', [CompetitionController::class, 'reject'])->name('competition.invites.reject');
  Route::post('/competition/{id}/force-join', [CompetitionController::class, 'forceJoin'])->name('competition.forceJoin');
  Route::get('/competition/details/{id}', [CompetitionController::class, 'details'])->name('competition.details');

  Route::get('/notification', [NotificationController::class, 'index'])->name('notifications');
  Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

  Route::get('/training', [TrainingController::class, 'index'])->name('training.show');
  Route::post('/training/submit', [TrainingController::class, 'submit'])->name('training.submit');
  Route::get('/training/attributes', [TrainingController::class, 'attributes'])->name('training.attributes.show');
  Route::post('/training/attributes/submit', [TrainingController::class, 'attributes_submit'])->name('training.attributes.submit');

  Route::get('/players/details/{id}', [PlayersController::class, 'details'])->name('player.details');
  Route::get('/player/lineup', [PlayerLineupController::class, 'lineup'])->name('player.lineup');
  Route::post('/player/lineup', [PlayerLineupController::class, 'save'])->name('lineup.save');


  // Forget Password Routes.
  Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
  Route::post('/password/reset/submit', [ForgotPasswordController::class, 'reset'])->name('password.update');

  //ajax
  Route::get('/clubs_by_competition/{competition_id}', [AjaxController::class, 'getClubsByCompetition']);
  //ajax
  // Add this route for fetching registered players
  Route::get('/match/registered-players', [MatchesController::class, 'getRegisteredPlayers'])
    ->name('match.registered_players');
})->middleware('auth:admin');

// Player authentication routes
Route::group(['prefix' => 'player', 'as' => 'player.'], function () {
  Route::get('/', [PlayerDashboardController::class, 'index'])->name('dashboard');
  Route::post('/dashboard/update/{id}', [PlayerDashboardController::class, 'update'])->name('dashboard.update');
  Route::put('/dashboard/update/{id}', [PlayerDashboardController::class, 'update'])->name('dashboard.update');
  Route::get('/playerdetails/{id}', [PlayerDashboardController::class, 'details'])->name('details');

  // Login Routes.
  Route::get('/login', [PlayerLoginController::class, 'showLoginForm'])->name('login');
  Route::post('/login/submit', [PlayerLoginController::class, 'login'])->name('login.submit');

  // Logout Routes.
  Route::post('/logout/submit', [PlayerLoginController::class, 'logout'])->name('logout.submit');

  Route::get('/password/reset', [PlayerForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
  Route::post('/password/reset/submit', [PlayerForgotPasswordController::class, 'reset'])->name('password.update');
})->middleware('auth:player');
