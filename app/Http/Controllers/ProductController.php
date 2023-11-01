<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Rules\OnlyArabic;
use App\Rules\OnlyEnglish;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\CategoryProduct;
use App\Http\Controllers\CategoryController;

//test if variable is associativeArray
// function isAssociativeArray($array) {
//     if (!is_array($array)) {
//         return false; // Not an array
//     }

//     // Check if the array has at least one non-integer key
//     $keys = array_keys($array);
//     foreach ($keys as $key) {
//         if (!is_int($key)) {
//             return true; // At least one non-integer key found, it's an associative array
//         }
//     }

//     return false; // All keys are integers, it's not an associative array
// }

function hasAssociativeArray($array)
{
    foreach ($array as $element) {
        if (is_array($element) && count(array_filter(array_keys($element), 'is_string')) > 0) {
            return true; // Element is an associative array
        }
    }
    return false; // No element in the array is an associative array
}


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'products appear successfully',
            'result' => Product::withTrashed()->orderBy('created_at', 'desc')->get()->load(['card_codes', 'categories', 'reviews']),
            'error' => null,
        ], 200);
    }

    /**
     * showWithOutTrashed
     */
    public function indexWithOutTrashed()
    {
        return response()->json([
            'success' => true,
            'message' => 'products appear successfully',
            'result' => Product::with(['card_codes', 'categories', 'reviews'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'error' => null,
        ], 200);
    }

    /**
     * showWithOutTrashed
     */
    public function indexTrashedOnly()
    {
        return response()->json([
            'success' => true,
            'message' => 'products appear successfully',
            'result' => Product::onlyTrashed()->get(),
            'error' => null,
        ], 200);
    }

    /**
     * only services
     */
    public function indexServices()
    {
        // Find the category by its name
        $category = Category::where('category_name', "جميع الخدمات")->first();

        if (!$category) {
            // Handle the case where the category doesn't exist
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'result' => [],
            ], 404);
        }

        // Retrieve products associated with the category including trashed products and their categories
        $products = $category->products()->with('categories')->get();

        if ($products->isEmpty()) {
            // Handle the case where no products are found in the category
            return response()->json([
                'success' => false,
                'message' => 'No products found in this category',
                'result' => [],
            ], 200);
        }

        // Return the products associated with the category
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'result' => $products,
        ], 200);
    }

    /**
     * only indexDigitalCards
     */
    public function indexDigitalCards()
    {
        // Find the category by its name
        $category = Category::where('category_name', 'بطاقات الألعاب')->first();

        $category2 = Category::Where('category_name', 'البطاقات الأخرى')->first();

        if (!$category && !$category2) {
            // Handle the case where the category doesn't exist
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'result' => [],
            ], 404);
        }
        // Retrieve products associated with the category including trashed products and their categories
        $products = $category->products()->with('categories')->get();
        $products2 = $category2->products()->with('categories')->get();

        $mergedProducts = array_merge($products->toArray(), $products2->toArray());

        if (empty($mergedProducts)) {
            // Handle the case where no products are found in the category
            return response()->json([
                'success' => false,
                'message' => 'No products found in this category',
                'result' => [],
            ], 200);
        }

        // Return the products associated with the category
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'result' => $products,
        ], 200);
    }

    /**
     * Display a listing of the indexByCategory.
     */
    public function get_by_category_name(Request $request)
    {
        $categoryName = $request->input("category");
        // Find the category by its name
        $category = Category::where('category_name', $categoryName)->first();

        if (!$category) {
            // Handle the case where the category doesn't exist
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'result' => [],
            ], 404);
        }

        // Retrieve products associated with the category including trashed products and their categories
        $products = $category->products()->withTrashed()->with('categories')->get();

        if ($products->isEmpty()) {
            // Handle the case where no products are found in the category
            return response()->json([
                'success' => false,
                'message' => 'No products found in this category',
                'result' => [],
            ], 200);
        }

        // Return the products associated with the category
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'result' => $products,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'description' => 'required',
            'usd_price' => 'required|numeric',
            'qar_price' => 'required|numeric',
            'stock_quantity' => 'numeric',
            'image' => 'required|image', // Assuming you're uploading images
            'categories' => 'required|array',
        ]);

        if ($request->hasFile('image')) {

            $image_name = Str::random() . '.' . $request->file('image')->getClientOriginalName();

            $request->file('image')->storeAs('public/images/products/', $image_name);
        } else {
            // Handle the case where no image is uploaded
            $image_name = 'default.jpg'; // Provide a default image or handle as needed
        }


        $product = new Product($request->only([
            'product_name', 'description', 'usd_price', 'qar_price', 'stock_quantity',
        ]));
        $product->image = $image_name;
        $res = $product->save();

        if ($res) {
            $categoryIds = [];

            foreach ($request->input('categories') as $categoryName) {
                $category = CategoryController::showByCategoryName($categoryName);

                if ($category) {

                    $categoryIds[] = $category->id;
                }
            }
            $product->categories()->attach($categoryIds);


            return response()->json([
                'success' => true,
                'message' => 'تم إضافة منتج جديد بنجاح',
                'result' => $product,
                'error' => null,
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'لم تتم إضافة منتج جديد',
            'result' => null,
            'error' => null,
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Load the 'card_codes' relationship
        $product->load(['card_codes', 'categories', 'reviews']);

        return response()->json([
            "success" => true,
            "message" => "Get Product successfully",
            "result" => $product,
            "error" => null
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function showAllByCategoryName(Category $category)
    {
        return response()->json([
            "success" => true,
            "message" => "Get Product successfully",
            "result" => $category->products,
            "error" => null
        ], 200);
    }


    /**
     * Display the specified resource's Codes.
     */
    public function showCardCodes(Product $product)
    {
        $catProduct = CategoryProduct::where("product_id", $product->id)->first();

        if ($catProduct === null) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to show the codes.',
                'result' => null,
                'error' => 'Product not found.',
            ], 404);
        }

        $cat = Category::find($catProduct->category_id);

        if ($cat->is_service !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to show the code.',
                'result' => null,
                'error' => "this product is not a digital card",
            ], 400);
        }

        return response()->json([
            "success" => true,
            "message" => "Get Card Codes successfully",
            "result" => $product->card_codes,
            "error" => null
        ], 200);
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_name' => 'required',
            'description' => 'required',
            'usd_price' => 'required|numeric',
            'qar_price' => 'required|numeric',
            'stock_quantity' => 'numeric',
            'image' => '', // Assuming you're uploading images
            'categories' => 'required|array', // Ensure 'categories' is an array
        ]);

        //categories
        // Check if the 'categories' have changed
        $newCategoryNames = $request->input('categories');


        if (hasAssociativeArray($newCategoryNames)) {
            $categoryNames = [];

            foreach ($newCategoryNames as $category) {
                if (isset($category['category_name'])) {
                    $categoryNames[] = $category['category_name'];
                }
            }

            $newCategoryNames = $categoryNames;
            // Now, $categoryNames contains an array of 'category_name' values from the associative array.
        }

        $currentCategoryIds = $product->categories->pluck('id')->toArray();

        // Check if the 'categories' have changed
        $newCategoryIds = [];

        // Check if the 'categories' have changed
        foreach ($newCategoryNames as $categoryName) {
            $category = Category::where('category_name', $categoryName)->first();
            if ($category) {
                $newCategoryIds[] = $category->id;
            }
        }

        // Detach old categories that are not in the new list
        $categoriesToDetach = array_diff($currentCategoryIds, $newCategoryIds);
        if (!empty($categoriesToDetach)) {
            $product->categories()->detach($categoriesToDetach);
            // Attach new category relations
        }

        // Attach new category relations if there are changes
        if (count(array_diff($newCategoryIds, $currentCategoryIds)) > 0) {
            $product->categories()->sync($newCategoryIds);
        }

        // return $newCategoryNames;

        // Update the rest of the product data
        $product->fill($request->except('image', 'categories'));
        $product->save();

        //update image when user upload new image.
        if ($request->hasFile('image')) {
            if ($product->image) {
                $ar_exist = Storage::disk('public')->exists("images/products/$product->image");

                if ($ar_exist) {
                    Storage::disk('public')->delete("images/products/$product->image");
                }
            }

            $image_name = Str::random() . '.' . $request->file('image')->getClientOriginalName();

            // Store the images with the desired names in the storage disk
            $request->file('image')->storeAs('public/images/products', $image_name);

            $product->image = $image_name;


            $product->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'result' => Product::find($product->id),
            'error' => null,
        ], 200); // 201 Created status code



    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //delete category links with this product
        // foreach ($product->categories as $category) {
        //     $categoryProduct = CategoryProduct::where("category_id", $category->id)
        //         ->where("product_id", $product->id)
        //         ->first();
        //     $categoryProduct->delete();
        // }

        $res = $product->delete();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "Product deleted successfully",
                "result" => $product,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "Fail to delete Product",
            "result" => null,
            "error" => null
        ], 400);
    }

    public function destroyProducts(Request $request)
    {
        $product_ids = $request->input('product_ids');
        $error = null;

        foreach ($product_ids as $id) {
            $products_will_delete = Product::find($id);

            if (!$products_will_delete) {
                // Handle the case where the user is not found
                $error = response()->json([
                    "success" => false,
                    "message" => "لم يتم العثور على المنتج",
                    "result" => null,
                    "error" => "User not found"
                ], 404);
            } else {

                $res = $products_will_delete->delete(); // Soft delete
                if (!$res) {
                    $error = response()->json([
                        "success" => false,
                        "message" => "فشلت عملية الحذف",
                        "result" => null,
                        "error" => "Failed to soft delete user"
                    ], 400);
                }
            }
        }

        if ($error !== null) {
            return $error;
        }

        return response()->json([
            "success" => true,
            "message" => "تم حذف المنتجات الحددين بنجاح",
            "result" => null,
            "error" => null
        ], 200);
    }
}
