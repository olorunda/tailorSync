<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    /**
     * Mark the tour as completed for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeTour(Request $request)
    {
        $user = Auth::user();
        $pageName = $request->input('page_name');

        if ($pageName) {
            // Mark the specific page's tour as completed
            $user->completedTours()->firstOrCreate([
                'page_name' => $pageName
            ]);
        } else {
            // For backward compatibility, mark the entire tour as completed
            $user->tour_completed = true;
            $user->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Check if the tour for a specific page has been completed for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTourCompletion(Request $request)
    {
        $user = Auth::user();
        $pageName = $request->input('page_name');

        if (!$pageName) {
            return response()->json(['completed' => false]);
        }

        $completed = $user->hasCompletedTourForPage($pageName);

        return response()->json(['completed' => $completed]);
    }
}
