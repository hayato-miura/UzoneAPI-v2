<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default('未設定')->change(); // nameカラムのnullを許可する
            $table->string('password')->nullable()->change(); // passwordカラムのnullを許可する
            $table->char("onetime_token", 4)->nullable(); // ワンタイムトークン
            $table->dateTime("onetime_expiration")->nullable(); // ワンタイムトークンの有効期限
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default()->change();
            $table->string('password')->nullable(false)->change();
            $table->dropColumn("onetime_token");
            $table->dropColumn("onetime_expiration");
        });
    }
};