<?php

namespace App\Http\Controllers\PlayerBackend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::PLAYER_DASHBOARD;

    /**
     * show login form for player guard
     *
     * @return void
     */
    public function showLoginForm()
    {
        return view('playerbackend.auth.login');
    }


    /**
     * login player
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        // Validate Login Data
        $request->validate([
            'email' => 'required|max:50',
            'password' => 'required',
        ]);

        $user = Player::where('username', $request->email)
            ->where('status', 'ACTIVE') // Ensure the user has an active status
            ->first();

        // Attempt to login
        if ($user && Auth::guard('player')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            // Redirect to dashboard
          
            session()->flash('success', 'Successully Logged in !');
            return redirect()->route('player.dashboard');
        } else {
            // Search using username
            if ($user && Auth::guard('player')->attempt(['username' => $request->email, 'password' => $request->password], $request->remember)) {
                session()->flash('success', 'Successully Logged in !');
                return redirect()->route('player.dashboard');
            }
            // error
            session()->flash('error', 'Invalid email and password');
            return back();
        }
    }

    /**
     * logout player guard
     *
     * @return void
     */
    public function logout()
    {
        Auth::guard('player')->logout();
        return redirect()->route('player.login');
    }
}
