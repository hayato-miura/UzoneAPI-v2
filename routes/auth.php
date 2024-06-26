<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    /**
     **最初のメール入力画面を表示するルーティング
     */
    Route::get('first-auth', [RegisteredUserController::class, 'create'])
        ->name('auth.first-auth'); // 追加

    /**
     **トークンを含んだメールを送信するルーティング
     */
    Route::post('sendTokenEmail', [RegisteredUserController::class, 'sendTokenEmail'])
    ->name('sendTokenEmail');
    /**
     **ワンタイムトークンが正しいか確かめて会員情報登録に遷移させるルーティング
     */
    Route::post('login', [RegisteredUserController::class, 'auth']);

    // // '/dashboard'へのGETリクエストに対して、ダッシュボードビューを表示します。
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/dashboard', function () {
        return view('auth.register');
    });

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    // ユーザー登録処理のルーティング
    Route::post('register', [RegisteredUserController::class, 'store']);

    // ログイン画面の表示
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    // ログイン処理のルーティング
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // パスワードリセットリンク要求画面の表示
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    // パスワードリセットリンクの送信処理
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    // パスワードリセット画面の表示
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    // パスワードリセット処理
    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

    // ログイン済みのユーザーのみアクセス可能なルート群
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [RegisteredUserController::class, 'dashboard'])->name('dashboard');
    // メール認証画面の表示
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    // メールアドレスの確認処理
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    // メール認証通知の再送信処理
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    // パスワード確認画面の表示
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    // パスワード確認処理
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    //パスワード変更処理
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // ログアウト処理
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// 共通ルート（認証状態に依存しない）
Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store']);