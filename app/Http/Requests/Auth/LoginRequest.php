<?php

// 認証関連のリクエストを扱うクラスの名前空間を定義します。
namespace App\Http\Requests\Auth;

// 必要なクラスをインポートします。
use Illuminate\Auth\Events\Lockout; // ログイン試行が多すぎるときに発火されるイベント。
use Illuminate\Foundation\Http\FormRequest; // フォームリクエストの基底クラス。
use Illuminate\Support\Facades\Auth; // 認証関連のファサード。
use Illuminate\Support\Facades\RateLimiter; // レートリミッターのファサード。
use Illuminate\Support\Str; // 文字列操作のためのクラス。
use Illuminate\Validation\ValidationException; // バリデーション例外クラス。

// ログインリクエストを扱うクラスを定義します。
class LoginRequest extends FormRequest
{
    /**
     * リクエストを行うユーザーがこのリクエストを実行する権限があるか判断します。
     */
    public function authorize(): bool
    {
        return true; // ここでは常にtrueを返していますが、実際には権限チェックのロジックを入れることもできます。
    }

    /**
     * リクエストに適用されるバリデーションルールを定義します。
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'], // メールアドレスは必須、文字列、メール形式である必要があります。
            'password' => ['required', 'string'], // パスワードは必須、文字列である必要があります。
        ];
    }

    /**
     * リクエストの認証情報でユーザーを認証しようと試みます。
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited(); // レートリミットを超えていないか確認します。

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // メールアドレスとパスワードでユーザー認証を試み、失敗した場合は例外を投げます。
            RateLimiter::hit($this->throttleKey()); // レートリミットカウントを増やします。

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'), // 認証失敗のメッセージを指定します。
            ]);
        }

        RateLimiter::clear($this->throttleKey()); // 認証に成功したら、レートリミットカウントをリセットします。
    }

    /**
     * ログインリクエストがレートリミットに達していないことを確認します。
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            // 許可された試行回数内であれば、何もしません。
            return;
        }

        event(new Lockout($this)); // ロックアウトイベントを発火させます。

        $seconds = RateLimiter::availableIn($this->throttleKey()); // 再試行可能になるまでの秒数を取得します。

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds, // 再試行可能になるまでの秒数。
                'minutes' => ceil($seconds / 60), // 再試行可能になるまでの分数。
            ]),
        ]);
    }

    /**
     * レートリミットのキーを取得します。
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}