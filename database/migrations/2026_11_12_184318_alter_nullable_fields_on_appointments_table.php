<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {

            $table->string('from_time_type')
                ->nullable()
                ->change();

            $table->string('to_time_type')
                ->nullable()
                ->change();

            $table->string('payable_amount')
                ->nullable()
                ->change();

        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {

            $table->string('from_time_type')
                ->nullable(false)
                ->change();

            $table->string('to_time_type')
                ->nullable(false)
                ->change();

            $table->string('payable_amount')
                ->nullable(false)
                ->change();

        });
    }
};