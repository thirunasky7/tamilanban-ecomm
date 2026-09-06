<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shorts', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('is_active');
        });

        Schema::create('short_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'short_id']);
        });

        Schema::create('product_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_favorites');
        Schema::dropIfExists('short_likes');
        Schema::table('shorts', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};
