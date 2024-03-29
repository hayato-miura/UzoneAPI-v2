<?php

// 名前空間の設定。Auth関連のコントローラをグループ化。
namespace App\Http\Controllers\Auth;

// 必要なクラスをインポート。
use App\Http\Controllers\Controller; // 基本のコントローラクラス。
use Illuminate\Http\RedirectResponse; // リダイレクトを扱うためのクラス。
use Illuminate\Http\Request; // HTTPリクエストを扱うためのクラス。

// EmailVerificationNotificationControllerクラスを定義。基本のコントローラクラスを継承。
class EmailVerificationNotificationController extends Controller
{
    /**
     * 新しいメール認証通知を送信する。
     */
    public function store(Request $request): RedirectResponse
    {
        // ユーザーがすでにメールを認証している場合は、ダッシュボードにリダイレクトする。
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // ユーザーがメールをまだ認証していない場合、認証メールの送信をトリガーする。
        $request->user()->sendEmailVerificationNotification();

        // 前のページに戻り、ステータスメッセージとして「verification-link-sent」をセッションに格納。
        return back()->with('status', 'verification-link-sent');
    }
}