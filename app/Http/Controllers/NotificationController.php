<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'notifications appear successfully',
            'result' => Notification::with(["user"])->orderBy("created_at", "desc")->get(),
            'error' => null,
        ], 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        return response()->json([
            'success' => true,
            'message' => 'Get notif successfully',
            'result' => $notification->load(['user']),
            'error' => null,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        // Update the notification to mark it as read
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Get notif successfully',
            'result' => Notification::with(["user"])->orderBy("created_at", "desc")->get(),
            'error' => null,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
    }
}
