<?php

namespace App\Http\Controllers\PlayerBackend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PlayerRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function index(Request $request)
    {
        $token = $request->get('token');
        $player = Player::where('code',$token)->where('status','INVITED')->first();
        if($player)
        {
            return view('playerbackend.auth.register',array(
                'user' => $player
            ));

        }else{
            return view('playerbackend.auth.invalid_player');

        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function register(Request $request)
    {
        $request->validate([
            'email' => 'nullable|max:50',
            'code' => 'required',
            'name' => 'required',
            'password' => 'required',
            'confirm_password' => 'confirm_password',
        ]);

        $player = Player::where('code',$request->get('code'))
            ->where('email',$request->get('email'))
            ->where('status','INVITED')->first();
        if($player)
        {
           $player->name = $request->get('name');
           $player->status = 'ACTIVE';
           $player->password = Hash::make($request->password);
           $player->save();

           return redirect()->route('player.login')->with('success', 'You have successfully registered. You can now login.');

        }else{
            return view('playerbackend.auth.invalid_player');

        }
    }
}
