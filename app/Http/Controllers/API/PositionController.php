<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::all();

        if ($positions->isNotEmpty()) {

            return response()->json([
                'success' => true,
                'positions' => PositionResource::collection($positions)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Positions not found'
        ], 404);
    }
}
