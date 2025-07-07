<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\BusinessDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use App\Services\StorefrontService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class StorefrontController extends Controller
{
    /**
     * The storefront service instance.
     *
     * @var \App\Services\StorefrontService
     */
    protected $storefrontService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\StorefrontService $storefrontService
     * @return void
     */
    public function __construct(StorefrontService $storefrontService)
    {
        $this->storefrontService = $storefrontService;
    }
    /**
     * Display the store homepage.
     */
    public function index($slug)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);

        // Check if store is enabled
        if (!$this->storefrontService->isStoreEnabled($businessDetail)) {
            abort(404, 'Store not found');
        }

        $userId = $businessDetail->user_id;

        // Get featured products
        $featuredProducts = [];
        if ($businessDetail->store_show_featured_products) {
            $featuredProducts = $this->storefrontService->getFeaturedProducts($userId);
        }

        // Get new arrivals
        $newArrivals = [];
        if ($businessDetail->store_show_new_arrivals) {
            $newArrivals = $this->storefrontService->getNewArrivals($userId);
        }

        // Get custom designs
        $customDesigns = [];
        if ($businessDetail->store_show_custom_designs) {
            $customDesigns = $this->storefrontService->getCustomDesigns($userId);
        }

        // Get cart
        $cart = $this->storefrontService->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

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
    public function addToCart($slug, AddToCartRequest $request)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $validated = $request->validated();

        $result = $this->storefrontService->addToCart(
            $userId,
            $validated['product_id'],
            $validated['quantity'],
            $validated['options'] ?? [],
            $validated['custom_design_data'] ?? null
        );

        if (!$result) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart($slug, UpdateCartRequest $request)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $validated = $request->validated();

        $result = $this->storefrontService->updateCartItem(
            $userId,
            $validated['cart_item_id'],
            $validated['quantity']
        );

        return redirect()->back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart($slug, $cartItemId)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;

        $result = $this->storefrontService->removeCartItem($userId, $cartItemId);

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Display the checkout page.
     */
    public function checkout($slug)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;

        // Get cart
        $cart = $this->storefrontService->getCart($userId);

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('storefront.cart', $slug)
                ->with('error', 'Your cart is empty.');
        }

        // Get currency symbol
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

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
    public function processCheckout($slug, CheckoutRequest $request)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $validated = $request->validated();

        // Process checkout
        $result = $this->storefrontService->processCheckout($userId, $validated);

        if (!$result['success']) {
            return redirect()->route('storefront.cart', $slug)
                ->with('error', $result['message']);
        }

        // Handle payment method
        if ($result['payment_required'] && $businessDetail->payment_enabled) {
            // Redirect to payment page
            return redirect()->route('payment.order.pay', [
                'orderId' => $result['order']->id,
            ]);
        }
            // Cash on delivery or payment not enabled
            return redirect()->route('storefront.order.confirmation', [
                'slug' => $slug,
                'order' => $result['order']->id,
            ])->with('success', 'Order placed successfully.');

    }

    /**
     * Display the order confirmation page.
     */
    public function orderConfirmation($slug, $orderId)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;

        $order = $this->storefrontService->getOrderById($userId, $orderId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Get cart
        $cart = $this->storefrontService->getCart($userId);

        // Get currency symbol
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

        return view('storefront.order-confirmation', compact(
            'businessDetail',
            'order',
            'cart',
            'currencySymbol'
        ));
    }


    /**
     * Display the login form.
     */
    public function showLogin($slug)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $cart = $this->storefrontService->getCart($userId);
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

        return view('storefront.login', compact(
            'businessDetail',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Process the login request.
     */
    public function login($slug, LoginRequest $request)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);

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
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $cart = $this->storefrontService->getCart($userId);
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

        return view('storefront.register', compact(
            'businessDetail',
            'cart',
            'currencySymbol'
        ));
    }

    /**
     * Process the registration request.
     */
    public function register($slug, RegisterRequest $request)
    {
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $validated = $request->validated();

        $user = $this->storefrontService->registerUser($validated, $businessDetail->user_id);

        Auth::login($user);

        return redirect()->route('storefront.index', $slug);
    }

    /**
     * Log the user out.
     */
    public function logout($slug, Request $request)
    {
        // No need to use the service for logout as it's a simple operation
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
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $cart = $this->storefrontService->getCart($userId);
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

        // Get the authenticated user's orders
        $orders = $this->storefrontService->getClientOrders($userId, Auth::user()->email);

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
        $businessDetail = $this->storefrontService->getBusinessDetailBySlug($slug);
        $userId = $businessDetail->user_id;
        $cart = $this->storefrontService->getCart($userId);
        $currencySymbol = $this->storefrontService->getCurrencySymbol($userId);

        // Get the order and ensure it belongs to the authenticated user
        $order = $this->storefrontService->getClientOrderDetails($userId, Auth::user()->email, $orderId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        return view('storefront.order-details', compact(
            'businessDetail',
            'cart',
            'currencySymbol',
            'order'
        ));
    }
}
