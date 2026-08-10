<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The table already stored generic key/heading/data rows; only its name said
 * "homepage". About and Contact now live in it too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('home_sections', 'page_sections');
    }

    public function down(): void
    {
        Schema::rename('page_sections', 'home_sections');
    }
};
