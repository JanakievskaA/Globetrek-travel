<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('image');

            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();

            // A tour is either same-day (duration_hours) or multi-day (duration_days).
            $table->unsignedSmallInteger('duration_days')->default(0);
            $table->unsignedSmallInteger('duration_nights')->default(0);
            $table->unsignedSmallInteger('duration_hours')->nullable();

            $table->unsignedSmallInteger('group_size')->default(15);
            $table->unsignedTinyInteger('min_age')->default(0);
            $table->string('difficulty')->default('easy');
            $table->string('departure_point')->nullable();
            $table->string('contact_phone')->nullable();

            $table->json('languages')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->json('highlights')->nullable();
            $table->json('amenities')->nullable();
            $table->json('faqs')->nullable();
            $table->json('extras')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Denormalised aggregates, refreshed whenever reviews/bookings change.
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('bookings_count')->default(0);
            $table->unsignedInteger('views')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('published');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['destination_id', 'category_id']);
            $table->index('price');
            $table->index('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
