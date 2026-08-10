<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            // Matches a key in App\Support\HomeSections, which defines the
            // fields the admin screen renders for it.
            $table->string('key')->unique();
            $table->string('heading')->nullable();
            $table->text('subtitle')->nullable();
            // Everything beyond heading/subtitle: images, links, repeated cards.
            $table->json('data')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
