<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('duration')->nullable();
            $table->string('meals')->nullable();
            $table->string('accommodation')->nullable();
            $table->timestamps();

            $table->index(['tour_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_itineraries');
    }
};
