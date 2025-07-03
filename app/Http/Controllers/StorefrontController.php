<?php

namespace App\Http\Controllers;

use App\Models\BusinessDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    /**
     * Display the store homepage.
     */
    public function index($slug)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->firstOrFail();

        // Check if store is enabled
        if (!$businessDetail->store_enabled) {
            abort(404, 'Store not found');
        }

        // Check if the subscription plan allows store functionality
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = SubscriptionService::getPlan($planKey);

        if (!$plan) {
            abort(404, 'Store not found');
        }

        // Check if store is enabled in the subscription plan
        if (!($plan['features']['store_enabled'] ?? false)) {
            abort(404, 'Store not found');
        }

        // Check if subscription is active (except for free plan)
        if ($planKey !== 'free' && !SubscriptionService::isActive($businessDetail)) {
            abort(404, 'Store not found');
        }

        $userId = $businessDetail->user_id;

        // Get featured products
        $featuredProducts = [];
        if ($businessDetail->store_show_featured_products) {
            $featuredProducts = Product::where('user_id', $userId)
                ->where('is_featured', true)
                ->where('is_active', true)
                ->take(8)
                ->get();
        }

        // Get new arrivals
        $newArrivals = [];
        if ($businessDetail->store_show_new_arrivals) {
            $newArrivals = Product::where('user_id', $userId)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
        }

        // Get custom designs
        $customDesigns = [];
        if ($businessDetail->store_show_custom_designs) {
            $customDesigns = Product::where('user_id', $userId)
                ->where('is_active', true)
                ->where('is_custom_order', true)
                ->take(8)
                ->get();
        }

        // Get cart
        $cart = $this->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.index', compact(
            'businessDetail',
            'featuredProducts',
            'newArrivals',
            'customDesigns',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Display the products page.
     */
    public function products($slug, Request $request)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        // Build query
        $query = Product::where('user_id', $userId)
            ->where('is_active', true);

        // Apply category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Apply custom order filter
        if ($request->has('custom_order')) {
            $query->where('is_custom_order', true);
        } else {
            // By default, show regular products
            $query->where('is_custom_order', false);
        }

        // Apply price filter
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply sorting
        $sortBy = $request->sort ?? 'newest';
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Get products
        $products = $query->paginate(12);

        // Get categories for filter
        $categories = Product::where('user_id', $userId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Get cart
        $cart = $this->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.products', compact(
            'businessDetail',
            'products',
            'categories',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Display the product details page.
     */
    public function product($slug, $productId)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        $product = Product::where('id', $productId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->firstOrFail();

        // Get related products
        $relatedProducts = Product::where('user_id', $userId)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category', $product->category)
                    ->orWhere('is_custom_order', $product->is_custom_order);
            })
            ->take(4)
            ->get();

        // Get cart
        $cart = $this->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.product', compact(
            'businessDetail',
            'product',
            'relatedProducts',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Display the shopping cart page.
     */
    public function cart($slug)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        // Get cart
        $cart = $this->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.cart', compact(
            'businessDetail',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Add a product to the cart.
     */
    public function addToCart($slug, Request $request)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array',
            'custom_design_data' => 'nullable|array',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check if product belongs to the store
        if ($product->user_id !== $userId) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        // Get cart
        $cart = $this->getCart($userId);

        // Add product to cart
        $cart->addItem(
            $product,
            $validated['quantity'],
            $validated['options'] ?? [],
            $validated['custom_design_data'] ?? null
        );

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart($slug, Request $request)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        $validated = $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:0',
        ]);

        // Get cart
        $cart = $this->getCart($userId);

        // Find cart item
        $cartItem = $cart->items()->findOrFail($validated['cart_item_id']);

        // Update quantity
        if ($validated['quantity'] > 0) {
            $cart->updateItemQuantity($cartItem, $validated['quantity']);
        } else {
            $cart->removeItem($cartItem);
        }

        return redirect()->back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart($slug, $cartItemId)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        // Get cart
        $cart = $this->getCart($userId);

        // Find cart item
        $cartItem = $cart->items()->findOrFail($cartItemId);

        // Remove item
        $cart->removeItem($cartItem);

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Display the checkout page.
     */
    public function checkout($slug)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        // Get cart
        $cart = $this->getCart($userId);

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('storefront.cart', $slug)
                ->with('error', 'Your cart is empty.');
        }

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($userId);

        // Get client information if user is logged in
        $client = null;
        if (Auth::check()) {
            $client = \App\Models\Client::where('email', Auth::user()->email)
                ->where('user_id', $userId)
                ->first();
        }

        return view('storefront.checkout', compact(
            'businessDetail',
            'cart',
            'currencySymbol',
            'client'
        ));
    }

    /**
     * Process the checkout.
     */
    public function processCheckout($slug, Request $request)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:1000',
            'billing_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|string|in:online,cod',
        ]);

        // Get cart
        $cart = $this->getCart($userId);

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('storefront.cart', $slug)
                ->with('error', 'Your cart is empty.');
        }

        // Create client if not exists
        $client = \App\Models\Client::firstOrCreate(
            ['email' => $validated['email'], 'user_id' => $userId],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['shipping_address'],
            ]
        );

        // Create order
        $order = $cart->convertToOrder([
            'client_id' => $client->id,
            'shipping_address' => $validated['shipping_address'],
            'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
            'notes' => $validated['notes'],
            'payment_method' => $validated['payment_method'],
            'customer_name' => $validated['name'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'],
        ]);

        // Handle payment method
        if ($validated['payment_method'] === 'online' && $businessDetail->payment_enabled) {
            // Redirect to payment page
            return redirect()->route('payment.order.pay', [
                'orderId' => $order->id,
            ]);
        } else {
            // Cash on delivery or payment not enabled
            // Clear the cart session for COD orders
            Session::forget('cart_session_id');

            return redirect()->route('storefront.order.confirmation', [
                'slug' => $slug,
                'order' => $order->id,
            ])->with('success', 'Order placed successfully.');
        }
    }

    /**
     * Display the order confirmation page.
     */
    public function orderConfirmation($slug, $orderId)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;

        $order = \App\Models\Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->where('is_store_order', true)
            ->firstOrFail();

        // Get cart
        $cart = $this->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.order-confirmation', compact(
            'businessDetail',
            'order',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Get or create a shopping cart.
     */
    private function getCart($userId)
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
     */
    private function getCurrencySymbol($userId)
    {
        $user = \App\Models\User::find($userId);
        return $user ? $user->getCurrencySymbol() : '$';
    }

    /**
     * Display the login form.
     */
    public function showLogin($slug)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;
        $cart = $this->getCart($userId);
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.login', compact(
            'businessDetail',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Process the login request.
     */
    public function login($slug, Request $request)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('storefront.index', $slug);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Display the registration form.
     */
    public function showRegister($slug)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;
        $cart = $this->getCart($userId);
        $currencySymbol = $this->getCurrencySymbol($userId);

        return view('storefront.register', compact(
            'businessDetail',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Process the registration request.
     */
    public function register($slug, Request $request)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a client record for the user
        \App\Models\Client::firstOrCreate(
            ['email' => $request->email, 'user_id' => $businessDetail->user_id],
            [
                'name' => $request->name,
                'phone' => '',
                'address' => '',
            ]
        );

        Auth::login($user);

        return redirect()->route('storefront.index', $slug);
    }

    /**
     * Log the user out.
     */
    public function logout($slug, Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.index', $slug);
    }

    /**
     * Display the user's order history.
     */
    public function orderHistory($slug)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;
        $cart = $this->getCart($userId);
        $currencySymbol = $this->getCurrencySymbol($userId);

        // Find the client record associated with the authenticated user's email
        $client = \App\Models\Client::where('email', Auth::user()->email)
            ->where('user_id', $userId)
            ->first();

        // Get the authenticated user's orders
        $orders = collect();
        if ($client) {
            $orders = Order::where('client_id', $client->id)
                ->where('is_store_order', true)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('storefront.order-history', compact(
            'businessDetail',
            'cart',
            'currencySymbol',
            'orders'
        ));
    }

    /**
     * Display the details of a specific order.
     */
    public function orderDetails($slug, $orderId)
    {
        $businessDetail = BusinessDetail::where('store_slug', $slug)
            ->where('store_enabled', true)
            ->firstOrFail();

        $userId = $businessDetail->user_id;
        $cart = $this->getCart($userId);
        $currencySymbol = $this->getCurrencySymbol($userId);

        // Find the client record associated with the authenticated user's email
        $client = \App\Models\Client::where('email', Auth::user()->email)
            ->where('user_id', $userId)
            ->first();

        if (!$client) {
            abort(404, 'Order not found');
        }

        // Get the order and ensure it belongs to the authenticated user
        $order = Order::where('id', $orderId)
            ->where('client_id', $client->id)
            ->where('is_store_order', true)
            ->firstOrFail();

        return view('storefront.order-details', compact(
            'businessDetail',
            'cart',
            'currencySymbol',
            'order'
        ));
    }
}
