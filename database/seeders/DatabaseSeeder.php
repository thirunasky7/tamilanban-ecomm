<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use App\Models\Short;
use App\Models\User;
use App\Services\ProductVariantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage products',
            'manage categories',
            'manage banners',
            'manage shorts',
            'manage orders',
            'manage customers',
            'manage coupons',
            'manage settings',
            'manage payments',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superAdminRole = Role::findOrCreate('super_admin');
        $adminRole = Role::findOrCreate('admin');
        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions(array_diff($permissions, ['manage settings', 'manage payments']));

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@shopease.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        Setting::setValue('store_name', 'ShopEase', 'general');
        Setting::setValue('currency', 'INR', 'general');
        Setting::setValue('dummy_otp', '123456', 'auth');
        Setting::setValue('support_email', 'support@shopease.test', 'general');
        Setting::setValue('support_phone', '+91 98765 43210', 'general');
        Setting::setValue('default_locale', 'en', 'localization');
        Setting::setValue('locale_en_enabled', '1', 'localization', 'boolean');
        Setting::setValue('locale_ar_enabled', '1', 'localization', 'boolean');
        Setting::setValue('locale_switcher_enabled', '1', 'localization', 'boolean');

        PaymentGateway::query()->updateOrCreate(
            ['code' => 'cod'],
            ['name' => 'Cash on Delivery', 'is_enabled' => true, 'is_sandbox' => true]
        );
        PaymentGateway::query()->updateOrCreate(
            ['code' => 'razorpay'],
            [
                'name' => 'Razorpay',
                'is_enabled' => false,
                'is_sandbox' => true,
                'credentials' => [
                    'key_id' => '',
                    'key_secret' => '',
                    'upi' => true,
                    'netbanking' => true,
                    'card' => true,
                ],
            ]
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'SAVE10'],
            ['type' => 'percent', 'value' => 10, 'min_order' => 499, 'max_discount' => 500, 'is_active' => true]
        );
        Coupon::query()->updateOrCreate(
            ['code' => 'FLAT200'],
            ['type' => 'fixed', 'value' => 200, 'min_order' => 999, 'is_active' => true]
        );

        $categories = [
            ['name' => 'Sarees', 'image' => 'https://images.unsplash.com/photo-1610030469668-4eec0d0c0c0e?w=400'],
            ['name' => 'Electronics', 'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=400'],
            ['name' => 'Masala & Spices', 'image' => 'https://images.unsplash.com/photo-1596040033229-a0b516c5e6a0?w=400'],
            ['name' => 'Home & Kitchen', 'image' => 'https://images.unsplash.com/photo-1484101403633-562f91b996bc?w=400'],
            ['name' => 'Beauty', 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400'],
        ];

        foreach ($categories as $i => $category) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'image' => $category['image'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }

        $sarees = Category::query()->where('slug', 'sarees')->first();
        $electronics = Category::query()->where('slug', 'electronics')->first();
        $masala = Category::query()->where('slug', 'masala-spices')->first();

        $products = [
            [
                'name' => 'Banarasi Silk Saree',
                'category_id' => $sarees?->id,
                'price' => 3499,
                'compare_at_price' => 4999,
                'thumbnail' => 'https://images.unsplash.com/photo-1610030469668-4eec0d0c0c0e?w=600',
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'name' => 'Kanjivaram Cotton Saree',
                'category_id' => $sarees?->id,
                'price' => 2199,
                'compare_at_price' => 2999,
                'thumbnail' => 'https://images.unsplash.com/photo-1583391733981-5c3c0d0e0b0e?w=600',
                'is_bestseller' => true,
            ],
            [
                'name' => 'Designer Party Wear Saree',
                'category_id' => $sarees?->id,
                'price' => 4299,
                'compare_at_price' => 5999,
                'thumbnail' => 'https://images.unsplash.com/photo-1617629632578-4b8b5d4e5b0e?w=600',
                'is_featured' => true,
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'category_id' => $electronics?->id,
                'price' => 2999,
                'compare_at_price' => 3999,
                'thumbnail' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600',
                'is_featured' => true,
                'is_bestseller' => true,
            ],
            [
                'name' => 'Smart Watch Lite',
                'category_id' => $electronics?->id,
                'price' => 4499,
                'compare_at_price' => 5999,
                'thumbnail' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600',
                'is_new' => true,
            ],
            [
                'name' => 'Premium Garam Masala 500g',
                'category_id' => $masala?->id,
                'price' => 249,
                'compare_at_price' => 299,
                'thumbnail' => 'https://images.unsplash.com/photo-1596040033229-a0b516c5e6a0?w=600',
                'is_bestseller' => true,
                'is_featured' => true,
            ],
        ];

        foreach ($products as $i => $product) {
            $categoryIds = array_filter([$product['category_id'] ?? null]);
            unset($product['category_id']);

            $saved = Product::query()->updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                array_merge($product, [
                    'sku' => 'SKU-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'short_description' => 'Quality product from ShopEase.',
                    'description' => 'Premium quality item curated for everyday style and comfort.',
                    'stock' => 50,
                    'gallery' => [$product['thumbnail']],
                    'is_active' => true,
                    'sort_order' => $i + 1,
                    'is_featured' => $product['is_featured'] ?? false,
                    'is_new' => $product['is_new'] ?? false,
                    'is_bestseller' => $product['is_bestseller'] ?? false,
                ])
            );

            if ($categoryIds !== []) {
                $saved->categories()->sync($categoryIds);
            }
        }

        $sizeAttr = Attribute::query()->updateOrCreate(['slug' => 'size'], ['name' => 'Size']);
        $colorAttr = Attribute::query()->updateOrCreate(['slug' => 'color'], ['name' => 'Color']);

        $sizes = collect(['S', 'M', 'L', 'XL'])->mapWithKeys(function ($value, $index) use ($sizeAttr) {
            return [$value => AttributeValue::query()->updateOrCreate(
                ['attribute_id' => $sizeAttr->id, 'slug' => Str::slug($value)],
                ['value' => $value, 'sort_order' => $index + 1]
            )];
        });

        $colors = collect([
            'Red' => 'red',
            'Blue' => 'blue',
            'Green' => 'green',
        ])->mapWithKeys(function ($slug, $value) use ($colorAttr) {
            static $index = 0;
            $index++;

            return [$value => AttributeValue::query()->updateOrCreate(
                ['attribute_id' => $colorAttr->id, 'slug' => $slug],
                ['value' => $value, 'sort_order' => $index]
            )];
        });

        $banarasi = Product::query()->where('slug', 'banarasi-silk-saree')->first();
        if ($banarasi) {
            $banarasi->update(['product_type' => 'variable']);
            app(ProductVariantService::class)->sync($banarasi, [$sizeAttr->id, $colorAttr->id], [
                [
                    'attribute_value_ids' => [$sizes['M']->id, $colors['Red']->id],
                    'sku' => 'BAN-M-RED',
                    'price' => 3499,
                    'compare_at_price' => 4999,
                    'stock' => 12,
                    'thumbnail' => $banarasi->thumbnail,
                    'is_active' => 1,
                ],
                [
                    'attribute_value_ids' => [$sizes['L']->id, $colors['Blue']->id],
                    'sku' => 'BAN-L-BLU',
                    'price' => 3599,
                    'compare_at_price' => 4999,
                    'stock' => 8,
                    'thumbnail' => $banarasi->thumbnail,
                    'is_active' => 1,
                ],
                [
                    'attribute_value_ids' => [$sizes['XL']->id, $colors['Green']->id],
                    'sku' => 'BAN-XL-GRN',
                    'price' => 3699,
                    'compare_at_price' => 5199,
                    'stock' => 5,
                    'thumbnail' => $banarasi->thumbnail,
                    'is_active' => 1,
                ],
            ]);
        }

        Banner::query()->updateOrCreate(
            ['title' => 'Summer Sale'],
            [
                'subtitle' => 'Up to 40% off bestsellers',
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200',
                'link' => '/products',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
        Banner::query()->updateOrCreate(
            ['title' => 'New Arrivals'],
            [
                'subtitle' => 'Fresh styles every week',
                'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1200',
                'link' => '/products?sort=newest',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $earbuds = Product::query()->where('slug', 'wireless-earbuds-pro')->first();
        Short::query()->updateOrCreate(
            ['title' => 'Earbuds unboxing'],
            [
                'description' => 'Quick look at Wireless Earbuds Pro',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'thumbnail' => $earbuds?->thumbnail,
                'product_id' => $earbuds?->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $reviewers = ['Priya S.', 'Rahul M.', 'Anjali K.', 'Vikram D.', 'Sneha P.'];
        $comments = [
            'Bahut accha product hai, quality amazing!',
            'Value for money. Delivery was fast.',
            'Exactly as shown in pictures. Happy with purchase.',
            'Good product but sizing could be better.',
            'Loved it! Will order again.',
        ];

        foreach (Product::all() as $product) {
            for ($r = 0; $r < 3; $r++) {
                ProductReview::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'reviewer_name' => $reviewers[$r],
                        'title' => 'Great buy',
                    ],
                    [
                        'rating' => rand(4, 5),
                        'comment' => $comments[$r],
                        'is_approved' => true,
                    ]
                );
            }
        }
    }
}