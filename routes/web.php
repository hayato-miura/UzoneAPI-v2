<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ルートディレクトリにアクセスしたときに、ウェルカムページを表示します。
Route::get('/', function () {
    return view('welcome');
});





// 認証されたユーザーのみがアクセスできるルートグループです。
Route::middleware('auth')->group(function () {
    // プロファイル編集ページを表示するためのルート。
    // '/profile'へのGETリクエストを処理し、ProfileControllerのeditメソッドを呼び出します。
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // プロファイル情報の更新を処理するためのルート。
    // '/profile'へのPATCHリクエストを処理し、ProfileControllerのupdateメソッドを呼び出します。
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // プロファイルの削除を処理するためのルート。
    // '/profile'へのDELETEリクエストを処理し、ProfileControllerのdestroyメソッドを呼び出します。
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Laravelの認証関連ルートをインクルードします。
// これは、認証に関する標準的なルート（ログイン、登録、パスワードリセットなど）を提供します。
require __DIR__.'/auth.php';