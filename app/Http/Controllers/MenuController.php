<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function getMenu(Request $request) {
        $user = auth()->user();
        $menu = config('menu');
        $role = strtolower($user->user_role->name);

        return response()->json([
            'success' => isset($user) && isset($menu[$role]),
            'data' => isset($user) && isset($menu[$role])
                        ? $menu[$role]
                        : []
        ]);
    }
}
