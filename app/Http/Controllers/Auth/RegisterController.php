<?php

// 名前空間の宣言。認証関連のコントローラーが配置される場所。
namespace App\Http\Controllers\Auth;

// 必要なクラスのインポート。
use App\Http\Controllers\Controller; // ベースコントローラクラス。
use App\Models\User; // Userモデル。
use Illuminate\Foundation\Auth\RegistersUsers; // ユーザー登録機能を提供するトレイト。
use Illuminate\Support\Facades\Hash; // パスワードのハッシュ化を行うためのファサード。
use Illuminate\Support\Facades\Validator; // バリデーション機能を提供するファサード。

// RegisterControllerクラスの定義。Controllerクラスを継承。
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | このコントローラーは、新規ユーザーの登録及びそのバリデーションと作成を担当します。
    | デフォルトでは、このコントローラーはトレイトを使用して、追加のコードなしに
    | この機能を提供します。
    |
    */

    use RegistersUsers; // 新規ユーザー登録機能を提供するトレイトを使用。

    /**
     * 登録後のユーザーのリダイレクト先。
     *
     * @var string
     */
    protected $redirectTo = '/home'; // 登録後にユーザーをリダイレクトするパス。

    /**
     * 新しいコントローラインスタンスの作成。
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest'); // ゲスト（未ログインユーザー）のみがアクセスできるようにミドルウェアを設定。
    }

    /**
     * 登録リクエストのバリデーションルールを取得。
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // 登録フォームから送信されたデータに対するバリデーションルールを定義。
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'], // 名前は必須、文字列、最大255文字。
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // メールアドレスは必須、文字列、メール形式、最大255文字、usersテーブル内でユニーク。
            'password' => ['required', 'string', 'min:8', 'confirmed'], // パスワードは必須、文字列、最小8文字、確認用パスワードと一致する必要がある。
        ]);
    }

    /**
     * 有効な登録後に新しいユーザーインスタンスを作成。
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Userモデルを使用して新しいユーザーレコードをデータベースに作成。
        return User::create([
            'name' => $data['name'], // ユーザー名
            'email' => $data['email'], // メールアドレス
            'password' => Hash::make($data['password']), // パスワード（ハッシュ化）
        ]);
    }
}