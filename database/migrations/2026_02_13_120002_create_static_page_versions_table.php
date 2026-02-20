<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_page_versions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('title');
            $table->longText('content');
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_page_versions');
    }
};
