<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Viewer access control & pay-per-view.
 * - access_expires_at: optional expiration for join eligibility (e.g. 24h after stream ends).
 * - amount_paid: coins paid for this booking (for paid streams); 0 for free.
 * - access_tier: future VIP / tier support (e.g. standard | vip).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livestream_bookings', function (Blueprint $table) {
            $table->timestamp('access_expires_at')->nullable()->after('booked_at');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('access_expires_at');
            $table->string('access_tier', 32)->nullable()->after('amount_paid'); // standard | vip (future)
        });
    }

    public function down(): void
    {
        Schema::table('livestream_bookings', function (Blueprint $table) {
            $table->dropColumn(['access_expires_at', 'amount_paid', 'access_tier']);
        });
    }
};
