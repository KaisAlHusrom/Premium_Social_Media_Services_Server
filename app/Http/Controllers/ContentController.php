<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Banners appear successfully',
            'result' => Content::with(["content_items"])->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => "required",
            'description' => "required",
            'image_name' => 'required|image',
        ]);

        if ($request->hasFile('image_name')) {
            $banner_name = Str::random() . '.' . $request->file('image_name')->getClientOriginalName();
            $request->file('image_name')->storeAs('public/images/contents/', $banner_name);
        } else {
            // Handle the case where no image is uploaded
            $banner_name = 'default.jpg'; // Provide a default image or handle as needed
        }

        $res = Content::create($request->post() + ['image_name' => $banner_name]);


        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المحتوى بنجاح',
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

    public function update(Request $request, Content $content)
    {
        $request->validate([
            'title' => "required",
            'description' => "required",
            'image_name' => '',
        ]);


        // Fill the category model with the request data
        $content->fill($request->input());
        // Save the category model to update the database
        $content->save();

        if ($request->hasFile("image_name")) {
            if ($content->image_name) {
                $exist = Storage::disk('public')->exists("images/contents/$content->image_name");
                if ($exist) {
                    Storage::disk('public')->delete("images/contents/$content->image_name");
                }
            }

            $image_name = Str::random() . '.' . $request->image_name->getClientOriginalName();
            // Store the images with the desired names in the storage disk
            $request->file('image_name')->storeAs('public/images/contents', $image_name);

            $content->image_name = $image_name;

            $content->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المحتوى',
            'result' => $content,
            'error' => null,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        $res = $content->delete();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "content deleted successfully",
                "result" => $content,
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
