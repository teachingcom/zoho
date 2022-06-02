<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZohoOauthTokensTable extends Migration
{
    public function up(): void
    {
        Schema::create('zoho_oauth_tokens', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('env');
            $table->string('user_mail');
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('access_token')->nullable();
            $table->string('grant_token')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->string('redirect_url')->nullable();
            $table->timestamp('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoho_oauth_tokens');
    }
}
