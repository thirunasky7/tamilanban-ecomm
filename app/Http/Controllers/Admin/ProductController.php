<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const VARIANT_IMAGE_LIMIT = 3;

    public function __construct(private ProductVariantService $variants)
    {
    }

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['categories', 'productVariants'])
            ->latest()
            ->get();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formData(new Product()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);

            $defaultStock = $this->extractDefaultVariantStock($request, $data);
            $data = $this->stripVariableSimpleFields($data);

            $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
            $data = $this->applyThumbnail($request, $data);
            $product = Product::query()->create($data);
            $product->categories()->sync($categoryIds);

            if ($product->isVariable()) {
                $this->variants->sync(
                    $product,
                    $request->input('attribute_ids', []),
                    $this->prepareVariations($request, $defaultStock),
                );
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'categories',
            'attributes.values',
            'productVariants.attributeValues.attribute',
        ]);

        return view('admin.products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $product, $data) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);

            $defaultStock = $this->extractDefaultVariantStock($request, $data);
            $data = $this->stripVariableSimpleFields($data);

            $data = $this->applyThumbnail($request, $data, $product);
            $product->update($data);
            $product->categories()->sync($categoryIds);

            if ($product->isVariable()) {
                $this->variants->sync(
                    $product,
                    $request->input('attribute_ids', []),
                    $this->prepareVariations($request, $defaultStock),
                );
            } else {
            foreach ($product->productVariants as $variant) {
                $variant->deleteMedia();
            }
                $product->attributes()->detach();
                $product->productVariants()->delete();
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Media::delete($product->getRawOriginal('thumbnail'));
            foreach ($product->productVariants as $variant) {
                $variant->deleteMedia();
            }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function formData(Product $product): array
    {
        $attributes = Attribute::query()->with('values')->orderBy('name')->get();

        return [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'attributes' => $attributes,
            'attributesJson' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'values' => $attribute->values->map(function ($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }

    private function validated(Request $request): array
    {
        $isVariable = $request->input('product_type') === 'variable';

        $rules = [
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'product_type' => ['required', 'in:simple,variable'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'is_featured' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'variations' => ['nullable', 'array'],
            'variations.*.thumbnail' => ['nullable', 'string', 'max:500'],
            'variations.*.thumbnail_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'variations.*.gallery_files.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'variations.*.gallery.*' => ['nullable', 'string', 'max:500'],
            'variable_default_stock' => ['nullable', 'integer', 'min:0'],
        ];

        if ($isVariable) {
            $rules['sku'] = ['nullable', 'string', 'max:80'];
            $rules['price'] = ['nullable', 'numeric', 'min:0'];
            $rules['compare_at_price'] = ['nullable', 'numeric', 'min:0'];
            $rules['stock'] = ['nullable', 'integer', 'min:0'];
        } else {
            $rules['sku'] = ['nullable', 'string', 'max:80'];
            $rules['price'] = ['required', 'numeric', 'min:0'];
            $rules['compare_at_price'] = ['nullable', 'numeric', 'min:0'];
            $rules['stock'] = ['required', 'integer', 'min:0'];
        }

        $data = $request->validate($rules);

        if ($isVariable) {
            $request->validate([
                'attribute_ids' => ['required', 'array', 'min:1'],
                'attribute_ids.*' => ['integer', 'exists:attributes,id'],
                'variations' => ['required', 'array', 'min:1'],
            ]);

            $attributeIds = collect($request->input('attribute_ids', []))->map(fn ($id) => (int) $id)->all();
            $hasValidVariation = collect($request->input('variations', []))->contains(function ($row) use ($attributeIds) {
                $valueIds = collect($row['attribute_value_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();

                return filled($row['price'] ?? null)
                    && count($valueIds) === count($attributeIds);
            });

            if (! $hasValidVariation) {
                throw ValidationException::withMessages([
                    'variations' => 'Add at least one variation with a price and an option selected for each attribute.',
                ]);
            }
        }

        foreach (['is_featured', 'is_new', 'is_bestseller', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $data['price'] = $data['price'] ?? 0;
        $data['stock'] = $data['stock'] ?? 0;
        $data['category_ids'] = collect($request->input('category_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        unset($data['thumbnail_file']);

        return $data;
    }

    private function prepareVariations(Request $request, int $defaultStock = 0): array
    {
        $variations = $request->input('variations', []);

        foreach ($variations as $index => $row) {
            $existing = ! empty($row['id'])
                ? ProductVariant::query()->find($row['id'])
                : null;

            $rowStock = isset($row['stock']) && $row['stock'] !== ''
                ? (int) $row['stock']
                : null;

            if ($rowStock === null || $rowStock < 1) {
                if ($defaultStock > 0) {
                    $variations[$index]['stock'] = $defaultStock;
                } elseif ($existing) {
                    $variations[$index]['stock'] = (int) $existing->stock;
                } else {
                    $variations[$index]['stock'] = 0;
                }
            }

            $gallery = $existing ? $existing->galleryPaths() : [];

            if ($request->hasFile("variations.{$index}.thumbnail_file")) {
                if ($existing) {
                    Media::delete($existing->getRawOriginal('thumbnail'));
                    if (! empty($gallery[0])) {
                        Media::delete($gallery[0]);
                    }
                }

                $variations[$index]['thumbnail'] = Media::store(
                    $request->file("variations.{$index}.thumbnail_file"),
                    'products/variants'
                );
            } elseif (filled($row['thumbnail'] ?? null)) {
                $variations[$index]['thumbnail'] = $row['thumbnail'];
            } elseif ($existing) {
                $variations[$index]['thumbnail'] = $existing->getRawOriginal('thumbnail');
            }

            for ($slot = 1; $slot < self::VARIANT_IMAGE_LIMIT; $slot++) {
                $fileKey = "variations.{$index}.gallery_files.{$slot}";

                if ($request->hasFile($fileKey)) {
                    if (! empty($gallery[$slot])) {
                        Media::delete($gallery[$slot]);
                    }

                    $gallery[$slot] = Media::store(
                        $request->file($fileKey),
                        'products/variants'
                    );
                } elseif (array_key_exists($slot, $row['gallery'] ?? [])) {
                    $gallery[$slot] = filled($row['gallery'][$slot]) ? $row['gallery'][$slot] : null;
                    if ($gallery[$slot] === null && $existing && ! empty($existing->galleryPaths()[$slot])) {
                        Media::delete($existing->galleryPaths()[$slot]);
                    }
                }
            }

            if (! empty($variations[$index]['thumbnail'])) {
                $gallery[0] = $variations[$index]['thumbnail'];
            } elseif (! empty($gallery[0]) && empty($variations[$index]['thumbnail'] ?? null)) {
                $variations[$index]['thumbnail'] = $gallery[0];
            }

            $variations[$index]['gallery'] = array_values(array_filter(
                array_slice($gallery, 0, self::VARIANT_IMAGE_LIMIT),
                fn ($path) => filled($path)
            ));

            unset($variations[$index]['thumbnail_file'], $variations[$index]['gallery_files']);
        }

        return $variations;
    }

    private function extractDefaultVariantStock(Request $request, array $data): int
    {
        if (($data['product_type'] ?? $request->input('product_type')) !== 'variable') {
            return 0;
        }

        return max(0, (int) $request->input('variable_default_stock', 0));
    }

    private function stripVariableSimpleFields(array $data): array
    {
        if (($data['product_type'] ?? null) !== 'variable') {
            return $data;
        }

        unset($data['sku'], $data['price'], $data['compare_at_price'], $data['stock'], $data['variable_default_stock']);

        return $data;
    }

    private function applyThumbnail(Request $request, array $data, ?Product $product = null): array
    {
        if ($request->hasFile('thumbnail_file')) {
            if ($product) {
                Media::delete($product->getRawOriginal('thumbnail'));
            }
            $data['thumbnail'] = Media::store($request->file('thumbnail_file'), 'products');

            return $data;
        }

        if (! filled($data['thumbnail'] ?? null)) {
            unset($data['thumbnail']);
        }

        return $data;
    }
}
