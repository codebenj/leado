<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    /**
     * Get authenticated user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->with(['address', 'user_role', 'organisation_user', 'organisation_user.organisation'])->first();

        return response()->json($user);
    }
}
