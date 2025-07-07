<?php

namespace App\Services;

use App\Models\BusinessDetail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class StorefrontService
{
    /**
     * Get or create a shopping cart.
     *
     * @param int $userId
     * @return ShoppingCart
     */
    public function getCart(int $userId): ShoppingCart
    {
        // Get or generate session ID
        $sessionId = Session::get('cart_session_id');
        if (!$sessionId) {
            $sessionId = Str::uuid();
            Session::put('cart_session_id', $sessionId);
        }

        // Get or create cart
        $cart = ShoppingCart::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => $userId]
        );

        return $cart;
    }

    /**
     * Get the currency symbol for the store owner.
     *
     * @param int $userId
     * @return string
     */
    public function getCurrencySymbol(int $userId): string
    {
        $user = User::find($userId);
        return $user ? $user->getCurrencySymbol() : '$';
    }

    /**
     * Get business details by slug.
     *
     * @param string $slug
     * @return BusinessDetail
     */
    public function getBusinessDetailBySlug(string $slug): BusinessDetail
    {
        return BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();
    }

    /**
     * Add a product to the cart.
     *
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @param array $options
     * @param array|null $customDesignData
     * @return bool
     */
    public function addToCart(int $userId, int $productId, int $quantity, array $options = [], ?array $customDesignData = null): bool
    {
        $product = Product::findOrFail($productId);

        // Check if product belongs to the store
        if ($product->user_id !== $userId) {
            return false;
        }

        // Get cart
        $cart = $this->getCart($userId);

        // Add product to cart
        $cart->addItem(
            $product,
            $quantity,
            $options,
            $customDesignData
        );

        return true;
    }

    /**
     * Update cart item quantity.
     *
     * @param int $userId
     * @param int $cartItemId
     * @param int $quantity
     * @return bool
     */
    public function updateCartItem(int $userId, int $cartItemId, int $quantity): bool
    {
        // Get cart
        $cart = $this->getCart($userId);

        // Find cart item
        $cartItem = $cart->items()->findOrFail($cartItemId);

        // Update quantity
        if ($quantity > 0) {
            $cart->updateItemQuantity($cartItem, $quantity);
            return true;
        } else {
            $cart->removeItem($cartItem);
            return true;
        }

        return false;
    }

    /**
     * Remove an item from the cart.
     *
     * @param int $userId
     * @param int $cartItemId
     * @return bool
     */
    public function removeCartItem(int $userId, int $cartItemId): bool
    {
        // Get cart
        $cart = $this->getCart($userId);

        // Find cart item
        $cartItem = $cart->items()->findOrFail($cartItemId);

        // Remove item
        $cart->removeItem($cartItem);

        return true;
    }

    /**
     * Process checkout and create order.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function processCheckout(int $userId, array $data): array
    {
        // Get cart
        $cart = $this->getCart($userId);

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Your cart is empty.'
            ];
        }

        // Create client if not exists
        $client = Client::firstOrCreate(
            ['email' => $data['email'], 'user_id' => $userId],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['shipping_address'],
            ]
        );

        // Create order
        $order = $cart->convertToOrder([
            'client_id' => $client->id,
            'shipping_address' => $data['shipping_address'],
            'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
            'notes' => $data['notes'] ?? null,
            'payment_method' => $data['payment_method'],
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone'],
        ]);

        // Handle payment method
        if ($data['payment_method'] === 'online') {
            return [
                'success' => true,
                'payment_required' => true,
                'order' => $order
            ];
        } else {
            // Cash on delivery
            // Clear the cart session for COD orders
            Session::forget('cart_session_id');

            return [
                'success' => true,
                'payment_required' => false,
                'order' => $order
            ];
        }
    }

    /**
     * Get order by ID for a specific user.
     *
     * @param int $userId
     * @param int $orderId
     * @return Order|null
     */
    public function getOrderById(int $userId, int $orderId): ?Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->where('is_store_order', true)
            ->first();
    }

    /**
     * Get orders for a client.
     *
     * @param int $userId
     * @param string $clientEmail
     * @return \Illuminate\Support\Collection
     */
    public function getClientOrders(int $userId, string $clientEmail)
    {
        // Find the client record associated with the email
        $client = Client::where('email', $clientEmail)
            ->where('user_id', $userId)
            ->first();

        // Get the client's orders
        $orders = collect();
        if ($client) {
            $orders = Order::where('client_id', $client->id)
                ->where('is_store_order', true)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $orders;
    }

    /**
     * Get order details for a specific client.
     *
     * @param int $userId
     * @param string $clientEmail
     * @param int $orderId
     * @return Order|null
     */
    public function getClientOrderDetails(int $userId, string $clientEmail, int $orderId): ?Order
    {
        // Find the client record associated with the email
        $client = Client::where('email', $clientEmail)
            ->where('user_id', $userId)
            ->first();

        if (!$client) {
            return null;
        }

        // Get the order and ensure it belongs to the client
        return Order::where('id', $orderId)
            ->where('client_id', $client->id)
            ->where('is_store_order', true)
            ->first();
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @param int $storeOwnerId
     * @return User
     */
    public function registerUser(array $data, int $storeOwnerId): User
    {
        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Create a client record for the user
        Client::firstOrCreate(
            ['email' => $data['email'], 'user_id' => $storeOwnerId],
            [
                'name' => $data['name'],
                'phone' => '',
                'address' => '',
            ]
        );

        return $user;
    }

    /**
     * Get featured products for a store.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedProducts(int $userId, int $limit = 8)
    {
        return Product::where('user_id', $userId)
            ->where('is_featured', true)
            ->where('is_active', true)
            ->take($limit)
            ->get();
    }

    /**
     * Get new arrivals for a store.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewArrivals(int $userId, int $limit = 8)
    {
        return Product::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get custom designs for a store.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomDesigns(int $userId, int $limit = 8)
    {
        return Product::where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_custom_order', true)
            ->take($limit)
            ->get();
    }

    /**
     * Check if a store is enabled and subscription is valid.
     *
     * @param BusinessDetail $businessDetail
     * @return bool
     */
    public function isStoreEnabled(BusinessDetail $businessDetail): bool
    {
        // Check if store is enabled
        if (!$businessDetail->store_enabled) {
            return false;
        }

        // Check if the subscription plan allows store functionality
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = SubscriptionService::getPlan($planKey);

        if (!$plan) {
            return false;
        }

        // Check if store is enabled in the subscription plan
        if (!($plan['features']['store_enabled'] ?? false)) {
            return false;
        }

        // Check if subscription is active (except for free plan)
        if ($planKey !== 'free' && !SubscriptionService::isActive($businessDetail)) {
            return false;
        }

        return true;
    }
}
