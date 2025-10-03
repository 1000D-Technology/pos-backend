<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    
    public function index()
    {
        $units = Unit::all();
        return response()->json($units);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:units,name',
            'symbol' => 'required|string|max:255|unique:units,symbol',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unit = Unit::create($request->all());
        return response()->json($unit, 201);
    }

    
    public function show($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        return response()->json($unit);
    }

   
    public function update(Request $request, $id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:units,name,' . $id,
            'symbol' => 'required|string|max:255|unique:units,symbol,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unit->update($request->all());
        return response()->json($unit);
    }

    public function destroy($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->delete();
        return response()->json(['message' => 'Unit deleted successfully']);
    }

    
    public function search(Request $request)
    {
        $query = $request->input('name'); // Using 'name' as a general search term as per requirement

        if (!$query) {
            return response()->json(['message' => 'Please provide a search query (e.g., ?name=kg)'], 400);
        }

        $units = Unit::where('name', 'like', '%' . $query . '%')
                     ->orWhere('symbol', 'like', '%' . $query . '%')
                     ->get();

        return response()->json($units);
    }
}