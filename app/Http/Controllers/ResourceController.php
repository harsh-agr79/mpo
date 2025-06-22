<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource; // Assuming you have a Resource model
use Illuminate\Support\Facades\Storage;

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

        $filePath = $resource->path; // e.g., 'files/manual.pdf'

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download(Storage::disk('public')->path($filePath));
    }
}
