<?php

namespace ApurbaLabs\IAM\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * * Usage in Route: 
     * ->middleware('iam:invoice.view') // Single
     * ->middleware('iam:invoice.view|invoice.edit') // Multiple (OR logic)
     */
    public function handle(Request $request, Closure $next, string $permissions) // 👈 Changed to $permissions
    {
        $user = $request->user();

        // Check if user is logged in
        if (!$user) {
            return $request->expectsJson() 
                ? response()->json(['message' => 'Unauthenticated'], 401) 
                : abort(401);
        }

        /** 
         * Identify the Scope (The "Branch" or "Tenant")
         * Standard SaaS approach: Look for 'X-Scope-ID' in the header. 
         */
        $scopeId = $request->header('X-Scope-ID');

        // Split permissions by pipe | to support multiple checks
        $permissionArray = explode('|', $permissions);

        foreach ($permissionArray as $perm) {
            // If the user has ANY of the permissions, we let them through
            if (app('iam')->can($user, trim($perm), $scopeId)) {
                return $next($request);
            }
        }

        // Forbidden if no matches found
        return $request->expectsJson()
            ? response()->json([
                'error' => 'Forbidden',
                'message' => "You do not have the required permissions: {$permissions}"
            ], 403)
            : abort(403, 'Unauthorized');
    }
}