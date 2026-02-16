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
        Schema::table('livestream_bookings', function (Blueprint $table) {
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['livestream_id'])->references(['id'])->on('livestreams')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestream_bookings', function (Blueprint $table) {
            $table->dropForeign('livestream_bookings_user_id_foreign');
            $table->dropForeign('livestream_bookings_livestream_id_foreign');
        });
    }
};
