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

    public function download(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        return response()->download(storage_path('app/' . $resource->path));
    }
}
