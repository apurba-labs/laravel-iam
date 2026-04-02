<?php
namespace ApurbaLabs\IAM\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\IAM\Contracts\Authorizable;

class CheckController extends Controller
{
    public function check(Request $request)
    {
        $request->validate(['permission' => 'required|string']);

        $user = $request->user();

        // Ensure the user model follows your IAM rules
        if (!$user instanceof Authorizable) {
            return response()->json([
                'error' => 'User model does not implement IAM Authorizable interface.'
            ], 422);
        }

        return response()->json([
            'allowed' => app('iam')->can($user, $request->permission),
            'meta' => [
                'user_id' => $user->id,
                'permission' => $request->permission
            ]
        ]);
    }
}