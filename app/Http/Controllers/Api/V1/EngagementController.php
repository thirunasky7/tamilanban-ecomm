<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\CatalogTransformer;
use App\Models\Product;
use App\Models\ProductFavorite;
use App\Models\Short;
use App\Models\ShortLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EngagementController extends Controller
{
    public function favoriteIds(Request $request): JsonResponse
    {
        $ids = ProductFavorite::query()
            ->where('user_id', $request->user()->id)
            ->pluck('product_id')
            ->map(fn ($id) => (string) $id)
            ->values();

        return response()->json(['ids' => $ids]);
    }

    public function favorites(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', ProductFavorite::query()
                ->where('user_id', $request->user()->id)
                ->select('product_id'))
            ->with(['categories', 'approvedReviews', 'productVariants', 'attributes.values'])
            ->latest('id')
            ->get();

        return response()->json([
            'products' => CatalogTransformer::products($products),
        ]);
    }

    public function toggleFavorite(Request $request, int $productId): JsonResponse
    {
        $product = Product::query()->where('is_active', true)->findOrFail($productId);
        $userId = $request->user()->id;

        $existing = ProductFavorite::query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            ProductFavorite::query()->create([
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);
            $favorited = true;
        }

        return response()->json([
            'productId' => (string) $product->id,
            'favorited' => $favorited,
        ]);
    }

    public function toggleShortLike(Request $request, int $shortId): JsonResponse
    {
        $short = Short::query()->where('is_active', true)->findOrFail($shortId);
        $userId = $request->user()->id;

        $result = DB::transaction(function () use ($short, $userId) {
            $existing = ShortLike::query()
                ->where('user_id', $userId)
                ->where('short_id', $short->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                $short->decrement('likes_count');
                $liked = false;
            } else {
                ShortLike::query()->create([
                    'user_id' => $userId,
                    'short_id' => $short->id,
                ]);
                $short->increment('likes_count');
                $liked = true;
            }

            $short->refresh();

            return [
                'liked' => $liked,
                'likes' => (int) $short->likes_count,
            ];
        });

        return response()->json([
            'shortId' => (string) $short->id,
            'liked' => $result['liked'],
            'likes' => max(0, $result['likes']),
        ]);
    }
}
