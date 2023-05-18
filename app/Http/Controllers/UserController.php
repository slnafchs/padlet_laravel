<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function findById(string $id): JsonResponse
    {
        $user = User::where('id', $id)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        return $user != null ? response()->json($user, 200) : response()->json(null, 200);
    }
}
