<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * The product service instance.
     *
     * @var \App\Services\ProductService
     */
    protected $productService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\ProductService $productService
     * @return void
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $products = $this->productService->getUserProducts();

        return view('store.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('store.products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // Check subscription product limit
        $limitCheck = $this->productService->checkSubscriptionProductLimit(auth()->id());
        if ($limitCheck) {
            return redirect()->route($limitCheck['redirect'])
                ->with('error', $limitCheck['message'])
                ->with(array_filter($limitCheck, function ($key) {
                    return $key !== 'redirect' && $key !== 'message';
                }, ARRAY_FILTER_USE_KEY));
        }

        // Process images and create product
        $images = $this->productService->processImages($request);
        $product = $this->productService->createProduct($request->validated(), $images);

        return redirect()->route('store.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        if (!$this->productService->isAuthorized($product)) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        if (!$this->productService->isAuthorized($product)) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        if (!$this->productService->isAuthorized($product)) {
            abort(403, 'Unauthorized action.');
        }

        // Process images and update product
        $images = $this->productService->processImages($request, $product->images, $product->primary_image);
        $this->productService->updateProduct($product, $request->validated(), $images);

        return redirect()->route('store.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        if (!$this->productService->isAuthorized($product)) {
            abort(403, 'Unauthorized action.');
        }

        $this->productService->deleteProduct($product);

        return redirect()->route('store.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
