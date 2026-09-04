<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });

        if (Schema::hasColumn('products', 'category_id')) {
            DB::table('products')
                ->whereNotNull('category_id')
                ->orderBy('id')
                ->get()
                ->each(function (object $product) {
                    DB::table('category_product')->insertOrIgnore([
                        'category_id' => $product->category_id,
                        'product_id' => $product->id,
                    ]);
                });

            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });

            if ($this->indexExists('products', 'products_category_id_index')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex(['category_id']);
                });
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained()->nullOnDelete();
            $table->index('category_id');
        });

        DB::table('category_product')
            ->select('product_id', DB::raw('MIN(category_id) as category_id'))
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->get()
            ->each(function (object $row) {
                DB::table('products')
                    ->where('id', $row->product_id)
                    ->update(['category_id' => $row->category_id]);
            });

        Schema::dropIfExists('category_product');
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($item) => $item->name === $index);
        }

        $database = $connection->getDatabaseName();

        return ! empty($connection->select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index]
        ));
    }
};
