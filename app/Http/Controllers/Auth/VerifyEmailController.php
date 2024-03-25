<?php

// コントローラの名前空間を定義します。
namespace App\Http\Controllers\Auth;

// 必要なクラスをインポートします。
use App\Http\Controllers\Controller; // 基本的なコントローラ機能を提供するベースコントローラクラス
use Illuminate\Auth\Events\Verified; // ユーザーのメールアドレスが検証された時に発火するイベントクラス
use Illuminate\Foundation\Auth\EmailVerificationRequest; // メール検証リクエストを処理するためのカスタムリクエストクラス
use Illuminate\Http\RedirectResponse; // HTTPリダイレクトを行うためのレスポンスクラス

// 基本コントローラクラスを継承したVerifyEmailControllerクラスを定義します。
class VerifyEmailController extends Controller
{
    /**
     * 認証済みユーザーのメールアドレスを検証済みとしてマークします。
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // ユーザーのメールがすでに検証済みかをチェックします。
        if ($request->user()->hasVerifiedEmail()) {
            // 検証済みの場合、`auth.register`ルートにリダイレクトします（ここは実際のアプリケーションによって適宜変更ください）。
            return redirect()->intended(route('auth.register', absolute: false) . '?verified=1');
        }

        // ユーザーのメールアドレスを検証済みとしてマークし、成功した場合はVerifiedイベントを発火します。
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // ユーザーをダッシュボード（または指定されたリダイレクト先）にリダイレクトし、URLに`verified=1`クエリを追加します。
        return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
    }
}