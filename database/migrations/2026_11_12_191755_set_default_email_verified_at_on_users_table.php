<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE users
             MODIFY email_verified_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE users
             MODIFY email_verified_at TIMESTAMP NULL DEFAULT NULL'
        );
    }
};