<?php

namespace App\Http\Controllers;

use App\Models\MedicineInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineInventoryController extends Controller
{
    // GET all inventories
    public function index()
    {
        try {
            $inventories = MedicineInventory::with('category')->get();

            return response()->json([
                'status' => 'success',
                'data' => $inventories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch inventories',
            ], 500);
        }
    }

    // Store a new inventory
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory_number' => 'required|string|max:255',
            'medicine_category_id' => 'required|exists:medicine_categories,medicine_category_id',
            'total_medicine' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            $inventory = MedicineInventory::create($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $inventory,
                'message' => 'Inventory created successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create inventory',
            ], 500);
        }
    }

    // GET a specific inventory
    public function show($id)
    {
        try {
            $inventory = MedicineInventory::with('category')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $inventory
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found',
            ], 404);
        }
    }

    // Update a specific inventory
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'inventory_number' => 'sometimes|required|string|max:255',
            'medicine_category_id' => 'sometimes|required|exists:medicine_categories,medicine_category_id',
            'total_medicine' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            $inventory = MedicineInventory::findOrFail($id);
            $inventory->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $inventory,
                'message' => 'Inventory updated successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update inventory',
            ], 500);
        }
    }

    // Delete a specific inventory
    public function destroy($id)
    {
        try {
            $inventory = MedicineInventory::findOrFail($id);
            $inventory->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Inventory deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete inventory',
            ], 500);
        }
    }
}
