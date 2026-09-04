<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'is_new']);
            $table->index(['is_active', 'is_bestseller']);
            $table->index('category_id');
            $table->index('slug');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'is_approved']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('shorts', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropIndex(['is_active', 'is_new']);
            $table->dropIndex(['is_active', 'is_bestseller']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['slug']);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'is_approved']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
        });

        Schema::table('shorts', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
