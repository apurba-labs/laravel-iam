<?php
namespace ApurbaLabs\IAM\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\IAM\Models\Role;

class AssignmentController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = config('auth.providers.users.model');
    }

    public function assignRole($userId, Request $request)
    {
        $request->validate(['role' => 'required|exists:iam_roles,name']);
        
        $user = $this->userModel::findOrFail($userId);
        
        $user->assignRole($request->role);
        
        return response()->json(['status' => 'ok']);
    }

    public function assignPermission(Role $role, Request $request)
    {
        $request->validate(['permission_id' => 'required|exists:iam_permissions,id']);
        
        $role->permissions()->syncWithoutDetaching([$request->permission_id]);
        
        return response()->json(['status' => 'ok']);
    }
}
