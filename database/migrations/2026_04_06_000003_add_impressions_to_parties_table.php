<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            // Array of { user_id, impression }
            $table->json('party_impressions')->nullable();
            // Array of { movie_id, user_id, impression }
            $table->json('movie_impressions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['party_impressions', 'movie_impressions']);
        });
    }
};
