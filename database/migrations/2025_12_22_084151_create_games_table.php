<?php

use App\Enums\GameCategory;
use App\Enums\GameProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', [GameProvider::NETENT->value,GameProvider::PRAGMATIC->value]);
            $table->string('external_id');
            $table->string('title');
            $table->enum('category', [GameCategory::SLOTS->value, GameCategory::LIVE->value, GameCategory::TABLE->value]
            );
            $table->boolean('is_active')->default(true);
            $table->decimal('rtp', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['provider']);
            $table->index(['category']);
            $table->index(['is_active']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
