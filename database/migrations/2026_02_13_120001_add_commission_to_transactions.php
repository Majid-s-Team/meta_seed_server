<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('commission_amount')->default(0)->after('amount');
            $table->string('reference_type', 100)->nullable()->after('description'); // e.g. event_booking, livestream_booking
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'reference_type', 'reference_id']);
        });
    }
};
