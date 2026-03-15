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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();

            $table->string('youtube_id')->unique();
            $table->string('youtube_title')->nullable();

            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('channel_title')->nullable();

            $table->string('thumbnail_url')->nullable();

            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('category_id')->nullable();
            $table->json('tags')->nullable();

            $table->timestamp('youtube_published_at')->nullable();

            $table->boolean('is_embeddable')->default(true);
            $table->string('privacy_status')->nullable();
            $table->string('definition', 10)->nullable();
            $table->boolean('has_caption')->default(false);

            $table->timestamps();

            $table->index('title');
            $table->index('artist');
            $table->index('channel_title');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
