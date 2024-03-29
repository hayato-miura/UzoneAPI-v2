<?php

// 名前空間の定義。Auth関連のコントローラーをグループ化して整理。
namespace App\Http\Controllers\Auth;

// 必要なクラスのインポート。
use App\Http\Controllers\Controller; // Laravelの基本コントローラクラスを継承。
use Illuminate\Foundation\Auth\VerifiesEmails; // メール認証機能を提供するトレイト。

// VerificationControllerクラスの定義。Controllerクラスを継承。
class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | このコントローラーは、アプリケーションに最近登録したユーザーのメールアドレスの
    | 認証を処理するために責任を持ちます。元のメールメッセージを受け取らなかったユーザーに
    | メールを再送することも可能です。
    |
    */

    use VerifiesEmails; // メール認証機能をこのコントローラーに追加。

    /**
     * 認証後にユーザーをリダイレクトする先。
     *
     * @var string
     */
    protected $redirectTo = '/home'; // ユーザーを認証後に'/home'にリダイレクトする。

    /**
     * 新しいコントローラインスタンスを作成。
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); // 'auth'ミドルウェアを適用し、認証済みユーザーのみがアクセス可能に。
        $this->middleware('signed')->only('verify'); // 'verify'メソッドにのみ'signed'ミドルウェアを適用し、安全なURLからのアクセスを保証。
        $this->middleware('throttle:6,1')->only('verify', 'resend'); // 'verify'と'resend'メソッドにレートリミットを適用し、1分間に6回までの制限。
    }
}