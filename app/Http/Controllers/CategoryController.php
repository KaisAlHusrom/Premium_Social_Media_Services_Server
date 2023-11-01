<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Rules\OnlyArabic;
use App\Rules\OnlyEnglish;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Categories appear successfully',
            'result' => Category::orderBy('created_at', 'desc')->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Display a service of the resource.
     */
    public function indexServices()
    {
        return response()->json([
            'success' => true,
            'message' => 'Categories appear successfully',
            'result' => Category::where("is_service", 1)->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Display a service of the resource.
     */
    public function indexCards()
    {
        return response()->json([
            'success' => true,
            'message' => 'Categories appear successfully',
            'result' => Category::where("is_service", 0)->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => [
                'required',
                Rule::unique('categories', 'category_name'),
            ],
            'description',
            'is_service' => 'required|boolean',
            'category_icon' => "",
            'category_banner_image' => "required|image",
        ]);

        if ($request->hasFile('category_icon')) {
            $category_icon_name = Str::random() . '.' . $request->file('category_icon')->getClientOriginalName();
            $request->file('category_icon')->storeAs('public/images/categories/icons', $category_icon_name);
        } else {
            // Handle the case where no image is uploaded
            $category_icon_name = 'default.jpg'; // Provide a default image or handle as needed
        }

        if ($request->hasFile('category_banner_image')) {
            $category_banner_name = Str::random() . '.' . $request->file('category_banner_image')->getClientOriginalName();
            $request->file('category_banner_image')->storeAs('public/images/categories/banners', $category_banner_name);
        } else {
            // Handle the case where no image is uploaded
            $category_banner_name = 'default.jpg'; // Provide a default image or handle as needed
        }

        $res = Category::create($request->post() + ['category_icon' => $category_icon_name] + ["category_banner_image" => $category_banner_name]);


        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة التصنيف بنجاح',
                'result' => $res,
                'error' => null,
            ], 201); // 201 Created status code
        }

        return response()->json([
            'success' => false,
            'message' => 'فشلت عملية الإضافة',
            'result' => null,
            'error' => "There was an error while creating the category.",
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {

        $category->load('products');

        return response()->json([
            'success' => true,
            'message' => 'Category with associated products retrieved successfully',
            'result' => $category,
            'error' => null,
        ], 200);
    }

    /**
     * Display the specified resource with category name
     */
    public static function showByCategoryName(string $categoryName)
    {
        $category = Category::where("category_name", $categoryName)
            ->first();

        if ($category) {
            return $category;
        }
        return null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category_name' => 'required',
            'description',
            'is_service' => 'required|boolean',
            'category_icon' => "",
            'category_banner_image' => "",
        ]);

        if ($category->id === 1 || $category->id === 2 || $category->id === 3) {
            if ($request->category_name !== $category->category_name) {

                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تغيير اسم التصنيفات الرئيسية',
                    'result' => $category,
                    'error' => null,
                ], 400);
            }
        }

        // Fill the category model with the request data
        $category->fill($request->input());
        // Save the category model to update the database
        $category->save();

        if ($request->hasFile("category_banner_image")) {
            if ($category->category_banner_image) {
                $exist = Storage::disk('public')->exists("images/categories/banners/$category->category_banner_image");
                if ($exist) {
                    Storage::disk('public')->delete("images/categories/banners/$category->category_banner_image");
                }
            }

            $image_name = Str::random() . '.' . $request->category_banner_image->getClientOriginalName();
            // Store the images with the desired names in the storage disk
            $request->file('category_banner_image')->storeAs('public/images/categories/banners', $image_name);

            $category->category_banner_image = $image_name;

            $category->save();
        }

        if ($request->hasFile("category_icon")) {
            if ($category->category_icon) {
                $exist = Storage::disk('public')->exists("images/categories/icons/$category->category_icon");
                if ($exist) {
                    Storage::disk('public')->delete("images/categories/icons/$category->category_icon");
                }
            }

            $image_name = Str::random() . '.' . $request->category_icon->getClientOriginalName();
            // Store the images with the desired names in the storage disk
            $request->file('category_icon')->storeAs('public/images/categories/icons', $image_name);

            $category->category_icon = $image_name;

            $category->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'result' => $category,
            'error' => null,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->id !== 1 && $category->id !== 2 && $category->id !== 3) {
            if ($category->category_banner_image) {
                if ($category->category_banner_image) {
                    $exist = Storage::disk('public')->exists("images/categories/banners/$category->category_banner_image");
                    if ($exist) {
                        Storage::disk('public')->delete("images/categories/banners/$category->category_banner_image");
                    }
                }
            }

            if ($category->category_icon) {
                if ($category->category_icon) {
                    $exist = Storage::disk('public')->exists("images/categories/icons/$category->category_icon");
                    if ($exist) {
                        Storage::disk('public')->delete("images/categories/icons/$category->category_icon");
                    }
                }
            }

            $res = $category->delete();
            if ($res) {
                return response()->json([
                    "success" => true,
                    "message" => "Category deleted successfully",
                    "result" => $category,
                    "error" => null
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "Fail to delete category",
                "result" => null,
                "error" => null
            ], 400);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف التصنيفات الرئيسية',
                'result' => $category,
                'error' => null,
            ], 400);
        }
    }
}
