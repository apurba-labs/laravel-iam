<?php
namespace ApurbaLabs\IAM\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        if (!app('iam')->can($user, $permission)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}