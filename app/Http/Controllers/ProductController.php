<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::where('user_id', auth()->id());

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'featured') {
                $query->where('is_featured', true);
            } elseif ($status === 'custom') {
                $query->where('is_custom_order', true);
            }
        }

        // Apply sorting
        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } elseif ($sort === 'name_asc') {
                $query->orderBy('name', 'asc');
            } elseif ($sort === 'name_desc') {
                $query->orderBy('name', 'desc');
            } elseif ($sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($sort === 'stock_asc') {
                $query->orderBy('stock_quantity', 'asc');
            } elseif ($sort === 'stock_desc') {
                $query->orderBy('stock_quantity', 'desc');
            }
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(10)->withQueryString();

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_custom_order' => 'boolean',
            'images.*' => 'nullable|image|max:2048',
            'primary_image' => 'nullable|image|max:2048',
            'sizes' => 'nullable|array',
            'colors' => 'nullable|array',
            'materials' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        // Process images
        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagesPaths[] = $image->store('product-images', 'public');
            }
        }

        // Process primary image
        $primaryImagePath = null;
        if ($request->hasFile('primary_image')) {
            $primaryImagePath = $request->file('primary_image')->store('product-images', 'public');
        } elseif (!empty($imagesPaths)) {
            $primaryImagePath = $imagesPaths[0];
        }

        // Create product
        $product = Product::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? 'SKU-' . Str::random(8),
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'is_featured' => $validated['is_featured'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'is_custom_order' => $validated['is_custom_order'] ?? false,
            'images' => $imagesPaths,
            'primary_image' => $primaryImagePath,
            'sizes' => $validated['sizes'] ?? [],
            'colors' => $validated['colors'] ?? [],
            'materials' => $validated['materials'] ?? [],
            'tags' => $validated['tags'] ?? [],
        ]);

        return redirect()->route('store.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('store.products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_custom_order' => 'boolean',
            'images.*' => 'nullable|image|max:2048',
            'primary_image' => 'nullable|image|max:2048',
            'sizes' => 'nullable|array',
            'colors' => 'nullable|array',
            'materials' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        // Process images
        $imagesPaths = $product->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagesPaths[] = $image->store('product-images', 'public');
            }
        }

        // Process primary image
        $primaryImagePath = $product->primary_image;
        if ($request->hasFile('primary_image')) {
            $primaryImagePath = $request->file('primary_image')->store('product-images', 'public');
        } elseif (!empty($imagesPaths) && empty($primaryImagePath)) {
            $primaryImagePath = $imagesPaths[0];
        }

        // Update product
        $product->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? $product->sku,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'is_featured' => $validated['is_featured'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'is_custom_order' => $validated['is_custom_order'] ?? false,
            'images' => $imagesPaths,
            'primary_image' => $primaryImagePath,
            'sizes' => $validated['sizes'] ?? [],
            'colors' => $validated['colors'] ?? [],
            'materials' => $validated['materials'] ?? [],
            'tags' => $validated['tags'] ?? [],
        ]);

        return redirect()->route('store.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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

        $product->delete();

        return redirect()->route('store.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
