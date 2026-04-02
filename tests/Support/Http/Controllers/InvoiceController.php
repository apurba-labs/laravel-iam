<?php
namespace ApurbaLabs\IAM\Tests\Support\Http\Controllers;

use Illuminate\Http\Request;
use ApurbaLabs\IAM\Facades\IAM;

class InvoiceController
{
    public function approve(Request $request, $id)
    {
        $scopeId = $request->header('X-Scope-ID');
        $user = $request->user();

        // Manual check inside Controller
        if (!IAM::can($user, 'invoice.approve', $scopeId)) {
            return response()->json(['message' => 'Unauthorized for this scope'], 403);
        }

        return response()->json(['message' => "Invoice {$id} approved in scope {$scopeId}"]);
    }
}