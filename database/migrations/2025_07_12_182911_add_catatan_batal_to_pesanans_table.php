<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->text('catatan_batal')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn('catatan_batal');
        });
    }
};
