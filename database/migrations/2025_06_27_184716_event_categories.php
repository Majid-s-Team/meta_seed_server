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
        Schema::create('event_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// 2. Create events migration
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->date('date');
    $table->string('time');
    $table->integer('total_seats');
    $table->integer('available_seats');
    $table->integer('coins');
    $table->foreignId('category_id')->constrained('event_categories')->onDelete('cascade');
    $table->boolean('is_online')->default(false);
    $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
    $table->timestamps();
});

// 3. Create bookings migration
Schema::create('event_bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('event_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

// 4. Create wallet and transactions
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->integer('balance')->default(0);
    $table->timestamps();
});

Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type'); // debit or credit
    $table->integer('amount');
    $table->string('description');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
