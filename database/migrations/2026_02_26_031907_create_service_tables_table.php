<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_tables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('identifier');       // M-01, M-02, etc.
            $table->string('name')->nullable(); // opcional: Terraza, VIP 1, etc.

            $table->uuid('uuid')->unique()->default((string) Str::uuid());

            $table->string('status')->default('available');
            $table->boolean('is_active')->default(true);

            $table->string('current_session_token')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'identifier']);
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tables');
    }
};
