<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource; // Assuming you have a Resource model

class ResourceController extends Controller
{
    public function resources(Request $request)
    {
        $resources = Resource::all();

        return response()->json([
            'resources' => $resources,
        ]);
    }
}
