<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('thumbnail');
        });

        DB::table('product_variants')
            ->whereNotNull('thumbnail')
            ->orderBy('id')
            ->get()
            ->each(function (object $variant) {
                DB::table('product_variants')
                    ->where('id', $variant->id)
                    ->update(['gallery' => json_encode([$variant->thumbnail])]);
            });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};
