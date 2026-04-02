<?php
namespace ApurbaLabs\IAM\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\IAM\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:iam_roles,name'
        ]);

        return response()->json(Role::create($data), 201);
    }
}
