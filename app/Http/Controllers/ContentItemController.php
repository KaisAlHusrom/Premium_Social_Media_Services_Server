<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\ContentItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ContentItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content_id' => "required",
            'item' => "required",
            'item_icon' => '',
        ]);

        if ($request->hasFile('item_icon')) {
            $banner_name = Str::random() . '.' . $request->file('item_icon')->getClientOriginalName();
            $request->file('item_icon')->storeAs('public/images/contents/items_icons/', $banner_name);
        } else {
            // Handle the case where no image is uploaded
            $banner_name = 'default.jpg'; // Provide a default image or handle as needed
        }

        $res = ContentItem::create($request->post() + ['item_icon' => $banner_name]);


        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العنصر بنجاح',
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

    public function update(Request $request, ContentItem $contentItem)
    {
        $request->validate([
            'content_id' => "required",
            'item' => "required",
            'item_icon' => '',
        ]);


        // Fill the category model with the request data
        $contentItem->fill($request->input());
        // Save the category model to update the database
        $contentItem->save();

        if ($request->hasFile("item_icon")) {
            if ($contentItem->item_icon) {
                $exist = Storage::disk('public')->exists("images/contents/$contentItem->item_icon");
                if ($exist) {
                    Storage::disk('public')->delete("images/contents/$contentItem->item_icon");
                }
            }

            $image_name = Str::random() . '.' . $request->item_icon->getClientOriginalName();
            // Store the images with the desired names in the storage disk
            $request->file('item_icon')->storeAs('public/images/contents', $image_name);

            $contentItem->item_icon = $image_name;

            $contentItem->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المحتوى',
            'result' => $contentItem,
            'error' => null,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentItem $contentItem)
    {
        $res = $contentItem->delete();
        if ($res) {
            return response()->json([
                "success" => true,
                "message" => "item deleted successfully",
                "result" => $contentItem,
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
