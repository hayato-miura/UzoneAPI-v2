<?php

// 名前空間の設定。認証関連のコントローラーを格納。
namespace App\Http\Controllers\Auth;

// 必要なクラスの使用宣言。
use App\Http\Controllers\Controller; // ベースコントローラークラスを継承するため。
use Illuminate\Http\RedirectResponse; // リダイレクト応答を返すためのクラス。
use Illuminate\Http\Request; // HTTPリクエストを扱うためのクラス。
use Illuminate\View\View; // ビュー応答を返すためのクラス。

// EmailVerificationPromptControllerクラスの定義。Controllerクラスを継承。
class EmailVerificationPromptController extends Controller
{
    /**
     * メール認証プロンプトを表示する。
     *
     * ユーザーがメールアドレスを既に認証済みの場合は、ダッシュボード（または指定されたリダイレクト先）へリダイレクトする。
     * 未認証の場合は、メール認証を促すビューを表示する。
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // リクエストを行ったユーザーがメールアドレスを認証済みか確認。
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard', absolute: false)) // 認証済みの場合はダッシュボードへリダイレクト。
            : view('auth.verify-email'); // 未認証の場合はメール認証促進ビューを表示。
    }
}
