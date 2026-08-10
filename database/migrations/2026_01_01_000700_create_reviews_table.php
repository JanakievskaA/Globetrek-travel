<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->string('author_avatar')->nullable();

            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');

            $table->string('status')->default('pending');
            $table->unsignedInteger('helpful_count')->default(0);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->index(['tour_id', 'status']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
