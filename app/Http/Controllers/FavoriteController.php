<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $beachId = $request->input('beach_id');

        if (! $beachId) {
            return response()->json(['error' => 'Missing beach_id'], 422);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => __('common.favorite_login_required')], 401);
        }

        $isFavorited = $user->favorites()->where('beach_id', $beachId)->exists();

        if ($isFavorited) {
            $user->favorites()->detach($beachId);
        } else {
            $user->favorites()->attach($beachId);
        }

        return response()->json([
            'is_favorited' => ! $isFavorited,
        ]);
    }
}
