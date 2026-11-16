
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->longText('complaint')->nullable();
            $table->longText('diagnostic_hypothesis')->nullable();
            $table->longText('conduct')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'complaint',
                'diagnostic_hypothesis',
                'conduct',
            ]);
        });
    }
};
