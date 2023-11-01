<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Banners appear successfully',
            'result' => Banner::all(),
            'error' => null,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => "required",
            'title' => "",
            'image_name' => 'required|image',
            'is_appear' => "boolean",
        ]);

        if ($request->hasFile('image_name')) {
            $banner_name = Str::random() . '.' . $request->file('image_name')->getClientOriginalName();
            $request->file('image_name')->storeAs('public/images/banners/', $banner_name);
        } else {
            // Handle the case where no image is uploaded
            $banner_name = 'default.jpg'; // Provide a default image or handle as needed
        }

        $res = Banner::create($request->post() + ['image_name' => $banner_name]);


        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الخلفية بنجاح',
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
    public function show(Banner $banner)
    {
    }



    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'name' => "required",
            'title' => "",
            'image_name' => '',
            'is_appear' => "boolean",
        ]);


        // Fill the category model with the request data
        $banner->fill($request->input());
        // Save the category model to update the database
        $banner->save();

        if ($request->hasFile("image_name")) {
            if ($banner->image_name) {
                $exist = Storage::disk('public')->exists("images/banners/$banner->image_name");
                if ($exist) {
                    Storage::disk('public')->delete("images/banners/$banner->image_name");
                }
            }

            $image_name = Str::random() . '.' . $request->image_name->getClientOriginalName();
            // Store the images with the desired names in the storage disk
            $request->file('image_name')->storeAs('public/images/banners', $image_name);

            $banner->image_name = $image_name;

            $banner->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الخلفية',
            'result' => $banner,
            'error' => null,
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        $res = $banner->delete();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "banner deleted successfully",
                "result" => $banner,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "Fail to delete banner",
            "result" => null,
            "error" => null
        ], 400);
    }
}
