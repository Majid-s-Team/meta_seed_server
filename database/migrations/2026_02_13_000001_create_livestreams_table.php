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
        Schema::create('livestreams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['scheduled', 'live', 'ended'])->default('scheduled');
            $table->string('agora_channel');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('max_participants')->default(100);
            $table->unsignedBigInteger('created_by')->index('livestreams_created_by_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestreams');
    }
};
