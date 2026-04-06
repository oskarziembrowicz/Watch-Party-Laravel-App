<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('party_id')
                ->constrained('parties')
                ->cascadeOnDelete();

            // SECURITY: uploaded_by is not validated against party membership.
            // In production, verify the uploader is a party participant.
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // The original filename as provided by the client.
            // SECURITY: No sanitization is performed on this value.
            // In production, sanitize or reject suspicious filenames.
            $table->string('original_name');

            // The path on disk where the file is stored.
            $table->string('stored_path');

            // SECURITY: mime_type is taken from the upload and not independently verified.
            // In production, detect MIME type server-side (e.g. with finfo).
            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('size')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_files');
    }
};
