<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeBoundary;
use Illuminate\Http\Request;

class BoundaryLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'level' => ['required', 'integer', 'in:1,2,3,4'],
            'code' => ['required', 'string', 'max:20'],
        ]);

        $boundary = AdministrativeBoundary::where('level', $validated['level'])
            ->where('code', $validated['code'])
            ->first();

        $featureCollection = $boundary?->toFeatureCollection();
        if ($featureCollection === null) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'boundary' => $featureCollection,
            'center' => $boundary->center(),
            'displayName' => $boundary->name,
        ]);
    }
}
