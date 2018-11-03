<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\County;
use App\Traits\ActivationTrait;
use App\Traits\CaptchaTrait;
use App\Traits\CaptureIpTrait;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;
use App\Models\Profile;
use App\Models\Roleuser;

class RegisterController extends Controller
{
	
	public function showRegistrationForm()
    {
		$county = County::all();
    	return view('auth.register',compact('county'));
    }
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

    use ActivationTrait;
    use CaptchaTrait;
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/activate';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', [
            'except' => 'logout',
        ]);
    }
	

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data,
            [
                'name'                  => 'required',
                //'first_name'            => 'required',
                //'last_name'             => 'required',
                'email'                 => 'required|email|max:255|unique:users',
                'password'              => 'required|min:6|max:30|confirmed',
                'password_confirmation' => 'required|same:password',
				'phone'					=> 'required',
				'address'				=> 'required',
				'city'					=> 'required',
				'county'				=> 'required',
            ],
            [
                'email.required'                => trans('auth.emailRequired'),
                'email.email'                   => trans('auth.emailInvalid'),
                'password.required'             => trans('auth.passwordRequired'),
                'password.min'                  => trans('auth.PasswordMin'),
                'password.max'                  => trans('auth.PasswordMax'),
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $ipAddress = new CaptureIpTrait();
        $role = Role::where('slug', '=', 'donor')->first();
		$user = User::create([
                'name'        		=> $data['name'],
                'email'             => $data['email'],
				'phone'			   	=> $data['phone'],
				'address'		   	=> $data['address'],
				'city'		  		=> $data['city'],
				'county'			=> $data['county'],
                'password'          => Hash::make($data['password']),
                'token'             => str_random(64),
                'signup_ip_address' => $ipAddress->getClientIp(),
                'activated'         => !config('settings.activation'),
            ]);
		$profile = new Profile();
		$user->profile()->save($profile);
        $user->attachRole($role->id);
        $this->initiateEmailActivation($user);
        return $user;
    }
}