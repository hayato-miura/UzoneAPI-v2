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
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * @return View 登録画面のビュー
     */
    public function create(): View
    {
        return view('auth.first-auth');
    }

    /**
     **引数で渡されたメールアドレスとワンタイムトークンをusersテーブルに追加するコントロール
     * 新しいユーザーをusersテーブルに追加します。
     * ユーザーがまだ存在しない場合に使用します。
     * @param string $email ユーザーのメールアドレス
     * @param string $onetime_token 生成したワンタイムトークン
     * @param datetime $onetime_expiration ワンタイムトークンの有効期限
     */
    public static function storeEmailAndToken($email, $onetime_token, $onetime_expiration)
    {
        // Userモデルを使用して新しいレコードをデータベースに追加します。
        User::create([
            'email' => $email,
            'onetime_token' => $onetime_token,
            'onetime_expiration' => $onetime_expiration
        ]);
    }

    /**
     * ユーザーにワンタイムトークンを含むメールを送信し、トークン情報をデータベースに保存します。
     **引数で渡されたワンタイムトークンをusersテーブルに追加するコントロール
     * @param Request $request HTTPリクエスト
     * @return View 認証の第二段階のビュー
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
    * @param Request $request HTTPリクエスト
     * @return View 認証の第二段階のビュー
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
             // ユーザーが存在しない場合、新しいレコードを作成します。
            RegisteredUserController::storeEmailAndToken($email, $onetime_token, $onetime_expiration);
        } else {
            // ユーザーが既に存在する場合、トークンと有効期限を更新します。
            RegisteredUserController::storeToken($email, $onetime_token, $onetime_expiration);
        }

        session()->flash('email', $email); // 認証処理で利用するために一時的に格納

        // メール送信処理
        Mail::send(new TokenEmail($email, $onetime_token));
        return view('auth.second-auth');
    }

    /**
     **ワンタイムトークンが正しいか確かめてログインさせる
     * @param Request $request HTTPリクエスト
     * @return RedirectResponse ダッシュボードへのリダイレクト、または認証の最初の段階へのリダイレクト
     */
    public function auth(Request $request): RedirectResponse
    {
        $user = User::where('email', session('email'))->first();
        $expiration = new Carbon($user['onetime_token']);

        if ($user['onetime_token'] == $request->onetime_token && $expiration > now()) {
            Auth::login($user);
            return redirect()->route('dashboard');
        }
        Log::debug('message');
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