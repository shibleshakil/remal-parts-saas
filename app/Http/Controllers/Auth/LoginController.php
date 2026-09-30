<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
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
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The user just logged in: drop any stale branch context left in the
     * session (e.g. from a previous user's login) so active_branch_id()
     * resolves fresh from the new user's own assignment.
     */
    protected function authenticated($request, $user)
    {
        session()->forget('active_branch_id');

        // Super Admin may keep a null branch (global view); everyone else
        // starts pinned to their own branch.
        if (!$user->hasRole('Super Admin')) {
            session(['active_branch_id' => $user->branch_id]);
        }
    }

    protected function loggedOut($request)
    {
        session()->forget('active_branch_id');
    }
}
