<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\TokenEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.first-auth');
    }

    /**
     **引数で渡されたメールアドレスとワンタイムトークンをusersテーブルに追加するコントロール
     */
    public static function storeEmailAndToken($email, $onetime_token, $onetime_expiration)
    {
        User::create([
            'email' => $email,
            'onetime_token' => $onetime_token,
            'onetime_expiration' => $onetime_expiration
        ]);
    }

    /**
     **引数で渡されたワンタイムトークンをusersテーブルに追加するコントロール
     */
    public static function storeToken($email, $onetime_token, $onetime_expiration)
    {
        User::where('email', $email)->update([
            'onetime_token' => $onetime_token,
            'onetime_expiration' => $onetime_expiration
        ]);
    }
    /**
     **ワンタイムトークンが含まれるメールを送信する
     */
    public function sendTokenEmail(Request $request)
    {
        $email = $request->email;
        $onetime_token = "";

        for ($i = 0; $i < 4; $i++) {
            $onetime_token .= strval(rand(0, 9)); // ワンタイムトークン
        }
        $onetime_expiration = now()->addMinute(3); // 有効期限

        $user = User::where('email', $email)->first(); // 受け取ったメールアドレスで検索
        if ($user === null) {
            RegisteredUserController::storeEmailAndToken($email, $onetime_token, $onetime_expiration);
        } else {
            RegisteredUserController::storeToken($email, $onetime_token, $onetime_expiration);
        }

        session()->flash('email', $email); // 認証処理で利用するために一時的に格納

        Mail::send(new TokenEmail($email, $onetime_token));

        return view('auth.second-auth');
    }

    /**
     **ワンタイムトークンが正しいか確かめてログインさせる
     */
    public function auth(Request $request): RedirectResponse
    {
        $user = User::where('email', session('email'))->first();
        $expiration = new Carbon($user['onetime_token']);

        if ($user['onetime_token'] == $request->onetime_token && $expiration > now()) {
            Auth::login($user);
            return redirect()->route('dashboard');
        }
        return redirect()->route('auth.first-auth');
    }
}

//     /**
//      * Handle an incoming registration request.
//      *
//      * @throws \Illuminate\Validation\ValidationException
//      */
//     public function store(Request $request): RedirectResponse
//     {
//         $request->validate([
//             'name' => ['required', 'string', 'max:255'],
//             'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
//             'password' => ['required', 'confirmed', Rules\Password::defaults()],
//         ]);

//         $user = User::create([
//             'name' => $request->name,
//             'email' => $request->email,
//             'password' => Hash::make($request->password),
//         ]);

//         event(new Registered($user));

//         Auth::login($user);

//         return redirect(route('dashboard', absolute: false));
//     }
// }