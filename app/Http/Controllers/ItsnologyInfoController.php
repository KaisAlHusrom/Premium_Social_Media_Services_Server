<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\itsnologyInfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ItsnologyInfoController extends Controller
{


    /**
     * send value based key
     */

    function get_value_by_key($key)
    {
        $row = ItsnologyInfo::where("keys", $key)->first();

        if ($row) {
            return response()->json([
                "success" => true,
                "message" => "تم إحضار البيانات بنجاح",
                "result" => $row,
                "error" => null
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "فشل إحضار البيانات",
            "result" => null,
            "error" => null
        ], 404);
    }

    public function update(Request $request)
    {
        $requestData = $request->all();
        $keysToUpdate = array_keys($requestData);

        foreach ($keysToUpdate as $key) {
            $row = ItsnologyInfo::where("keys", $key)->first();

            if ($row) {
                // Check if it's a file upload request
                if ($request->hasFile($key)) {
                    // Handle file updates
                    $file = $request->file($key);

                    $existingFile = $row->values;

                    if ($existingFile) {
                        $filePath = "images/itsnology/$existingFile";

                        if (Storage::disk('public')->exists($filePath)) {
                            Storage::disk('public')->delete($filePath);
                        }
                    }

                    $image_name = Str::random() . '.' . $file->getClientOriginalName();
                    $file->storeAs('public/images/itsnology', $image_name);

                    $row->values = $image_name;
                } else {
                    // Update other fields
                    $row->values = $requestData[$key];
                }

                $row->save();
            }
        }

        return response()->json([
            "success" => true,
            "message" => "تم تحديث البيانات بنجاح",
            "result" => $row, // You may want to return the updated row
            "error" => null
        ], 200);
    }

    function getValues()
    {
        $data = itsnologyInfo:: // Replace 'your_table_name' with your actual table name
            select('keys', 'values')
            ->get();

        $result = [];

        foreach ($data as $item) {
            $result[$item->keys] = $item->values;
        }

        return response()->json([
            "success" => true,
            "message" => "تم جلب البيانات بنجاح",
            "result" => $result, // You may want to return the updated row
            "error" => null
        ], 200);
    }
}
