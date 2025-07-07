<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Check if the user has reached the product limit for their subscription plan.
     *
     * @param int $userId
     * @return array|null Returns null if limit not reached, otherwise returns an array with error details
     */
    public function checkSubscriptionProductLimit(int $userId): ?array
    {
        $user = auth()->user();
        $businessDetail = $user->businessDetail;

        // Get the current product count
        $currentProductCount = Product::where('user_id', $userId)->count();

        // Get the max products allowed for the subscription plan
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = SubscriptionService::getPlan($planKey);

        if (!$plan) {
            return [
                'redirect' => 'subscriptions.index',
                'message' => 'Invalid subscription plan. Please contact support.'
            ];
        }

        $maxProducts = $plan['features']['max_products'];

        // Check if the user has reached the product limit
        if ($maxProducts !== 'unlimited' && $currentProductCount >= $maxProducts) {
            return [
                'redirect' => 'store.products.index',
                'message' => "You have reached the maximum number of products ({$maxProducts}) allowed for your {$planKey} plan. Please upgrade your plan to add more products.",
                'subscription_limit_reached' => true,
                'subscription_feature' => 'product limit',
                'subscription_plan' => $planKey,
                'subscription_limit' => $maxProducts,
                'subscription_current' => $currentProductCount
            ];
        }

        return null;
    }

    /**
     * Process product images.
     *
     * @param Request $request
     * @param array|null $existingImages
     * @param string|null $existingPrimaryImage
     * @return array
     */
    public function processImages(Request $request, ?array $existingImages = null, ?string $existingPrimaryImage = null): array
    {
        // Process images
        $imagesPaths = $existingImages ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagesPaths[] = $image->store('product-images', 'public');
            }
        }

        // Process primary image
        $primaryImagePath = $existingPrimaryImage;
        if ($request->hasFile('primary_image')) {
            $primaryImagePath = $request->file('primary_image')->store('product-images', 'public');
        } elseif (!empty($imagesPaths) && empty($primaryImagePath)) {
            $primaryImagePath = $imagesPaths[0];
        }

        return [
            'images' => $imagesPaths,
            'primary_image' => $primaryImagePath
        ];
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @param array $images
     * @return Product
     */
    public function createProduct(array $data, array $images): Product
    {
        return Product::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'sku' => $data['sku'] ?? 'SKU-' . Str::random(8),
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'is_featured' => $data['is_featured'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'is_custom_order' => $data['is_custom_order'] ?? false,
            'images' => $images['images'],
            'primary_image' => $images['primary_image'],
            'sizes' => $data['sizes'] ?? [],
            'colors' => $data['colors'] ?? [],
            'materials' => $data['materials'] ?? [],
            'tags' => $data['tags'] ?? [],
        ]);
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @param array $images
     * @return bool
     */
    public function updateProduct(Product $product, array $data, array $images): bool
    {
        return $product->update([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? $product->sku,
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'is_featured' => $data['is_featured'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'is_custom_order' => $data['is_custom_order'] ?? false,
            'images' => $images['images'],
            'primary_image' => $images['primary_image'],
            'sizes' => $data['sizes'] ?? [],
            'colors' => $data['colors'] ?? [],
            'materials' => $data['materials'] ?? [],
            'tags' => $data['tags'] ?? [],
        ]);
    }

    /**
     * Delete a product and its associated images.
     *
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Product $product): bool
    {
        // Delete product images
        if (!empty($product->images)) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        // Delete primary image if it's not in the images array
        if (!empty($product->primary_image) && !in_array($product->primary_image, $product->images ?? [])) {
            Storage::disk('public')->delete($product->primary_image);
        }

        return $product->delete();
    }

    /**
     * Get products for the authenticated user with filtering and pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserProducts()
    {
        $query = Product::where('user_id', auth()->id())->filter();

        // Get subscription plan details
        $user = auth()->user();
        $businessDetail = $user->businessDetail;
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = SubscriptionService::getPlan($planKey);
        $maxProducts = $plan['features']['max_products'] ?? 0;

        // If max_products is not unlimited, limit the number of products displayed
        if ($maxProducts !== 'unlimited' && $maxProducts > 0) {
            $query->limit($maxProducts);
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Check if the user is authorized to manage the product.
     *
     * @param Product $product
     * @return bool
     */
    public function isAuthorized(Product $product): bool
    {
        return $product->user_id === auth()->id();
    }
}
