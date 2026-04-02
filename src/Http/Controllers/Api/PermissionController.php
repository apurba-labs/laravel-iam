<?php
namespace ApurbaLabs\IAM\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\IAM\Models\Permission;

class PermissionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|unique:iam_permissions,name',
            'resource' => 'required|string',
            'action'   => 'required|string',
        ]);

        return response()->json(Permission::create($data), 201);
    }
}
