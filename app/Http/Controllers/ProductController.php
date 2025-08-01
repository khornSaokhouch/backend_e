<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        // Return all products for any authenticated user
        return response()->json(Product::with('productItems')->get());
    }
    
    public function show($id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        // Return the product — no owner restriction, everyone can see it
        return response()->json($product);
    }
    


    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'store_id'          => 'required|exists:stores,id',
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'product_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'price'             => 'required|numeric|min:0',
            'quantity_in_stock' => 'nullable|integer|min:0',
        ]);
    
        // Add user_id from authenticated user
        $validated['user_id'] = $request->user()->id;
    
        // Handle image upload
        if ($request->hasFile('product_image')) {
            $path = $request->file('product_image')->store('product_images', 'public');
            $validated['product_image'] = $path;
        }
    
        // Create the product
        $product = Product::create($validated);
    
        // Create associated ProductItem
        $quantity = $validated['quantity_in_stock'] ?? 0;
        $productItem = ProductItem::create([
            'product_id'        => $product->id,
            'quantity_in_stock' => $quantity,
        ]);
    
        return response()->json([
            'message'       => 'Product created successfully',
            'product'       => $product->makeHidden(['product_image'])->append('product_image_url'),
            'product_item'  => $productItem,
        ], 201);
    }
    
    
    public function update(Request $request, $id)
    {
        $product = Product::with('productItems')->findOrFail($id);
    
        $validated = $request->validate([
            'category_id'       => 'sometimes|exists:categories,id',
            'store_id'          => 'sometimes|exists:stores,id',
            'name'              => 'sometimes|string|max:255',
            'description'       => 'sometimes|nullable|string',
            'product_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,bmp,avif,svg,tiff|max:5120',
            'price'             => 'sometimes|numeric|min:0',
            'quantity_in_stock' => 'sometimes|integer|min:0',
        ]);
    
        if ($request->hasFile('product_image')) {
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }
    
            $path = $request->file('product_image')->store('product_images', 'public');
            $validated['product_image'] = $path;
        }
    
        $product->update($validated);
    
        if (array_key_exists('quantity_in_stock', $validated)) {
            $productItem = $product->productItems->first();
    
            if ($productItem) {
                $productItem->update(['quantity_in_stock' => $validated['quantity_in_stock']]);
            } else {
                ProductItem::create([
                    'product_id' => $product->id,
                    'quantity_in_stock' => $validated['quantity_in_stock'],
                ]);
            }
        }
    
        $product->refresh();
    
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->makeHidden(['product_image'])->append('product_image_url'),
        ], 200);
    }
    

public function destroy($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found.'], 404);
    }

    if ($product->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized.'], 403);
    }

    if ($product->product_image) {
        Storage::disk('public')->delete($product->product_image);
    }

    $product->delete();

    return response()->json([
        'message' => 'Product deleted successfully.',
    ], 200); // <-- Use 200 so you can see the response
}

    
}