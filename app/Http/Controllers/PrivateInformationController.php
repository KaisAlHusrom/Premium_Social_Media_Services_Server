<?php

namespace App\Http\Controllers;

use App\Models\PrivateInformation;
use Illuminate\Http\Request;

class PrivateInformationController extends Controller
{
    function getValues()
    {
        $data = PrivateInformation:: // Replace 'your_table_name' with your actual table name
            select('key', 'value')
            ->get();

        $result = [];

        foreach ($data as $item) {
            $result[$item->key] = $item->value;
        }

        return response()->json([
            "success" => true,
            "message" => "تم جلب البيانات بنجاح",
            "result" => $result, // You may want to return the updated row
            "error" => null
        ], 200);
    }

    public function update(Request $request)
    {
        $requestData = $request->all();
        $keysToUpdate = array_keys($requestData);

        foreach ($keysToUpdate as $key) {
            $row = PrivateInformation::where("key", $key)->first();

            if ($row) {
                // Check if it's a file upload request

                // Update other fields
                $row->value = $requestData[$key];


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
}
