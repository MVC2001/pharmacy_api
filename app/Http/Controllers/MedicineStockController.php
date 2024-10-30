<?php

namespace App\Http\Controllers;

use App\Models\MedicineStock;
use App\Models\MedicineInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MedicineStockController extends Controller
{
    // View all stocks
    public function index()
    {
        try {
            $stocks = MedicineStock::with(['inventory', 'category'])->get();
            return response()->json($stocks, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve stocks', 'error' => $e->getMessage()], 500);
        }
    }

    // Show stock by ID
    public function show($id)
    {
        try {
            $stock = MedicineStock::with(['inventory', 'category'])->findOrFail($id);
            return response()->json($stock, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Stock not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve stock', 'error' => $e->getMessage()], 500);
        }
    }

    // Add new stock
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:medicine_inventory,inventory_id',
            'medicine_category_id' => 'required|exists:medicine_category,medicine_category_id',
            'quantity_in_stock' => 'required|integer|min:1',
            'last_restocked' => 'required|date',
            'expiration_date' => 'required|date|after:last_restocked',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $inventory = MedicineInventory::findOrFail($request->inventory_id);

            // Check if the total_medicine is greater than the entered quantity_in_stock
            if ($inventory->total_medicine >= $request->quantity_in_stock) {
                // Create the stock entry
                $stock = MedicineStock::create([
                    'inventory_id' => $request->inventory_id,
                    'medicine_category_id' => $request->medicine_category_id,
                    'quantity_in_stock' => $request->quantity_in_stock,
                    'last_restocked' => $request->last_restocked,
                    'expiration_date' => $request->expiration_date,
                ]);
                return response()->json(['message' => 'Stock added successfully', 'data' => $stock], 201);
            }

            return response()->json(['message' => 'Quantity in stock exceeds total medicine available'], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add stock', 'error' => $e->getMessage()], 500);
        }
    }

    // Update stock
    public function update(Request $request, $id)
    {
        try {
            $stock = MedicineStock::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Stock not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity_in_stock' => 'sometimes|required|integer|min:1',
            'last_restocked' => 'sometimes|required|date',
            'expiration_date' => 'sometimes|required|date|after:last_restocked',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            // Update only if quantity_in_stock is provided and valid
            if ($request->has('quantity_in_stock')) {
                $inventory = MedicineInventory::findOrFail($stock->inventory_id);
                if ($inventory->total_medicine >= $request->quantity_in_stock) {
                    $stock->quantity_in_stock = $request->quantity_in_stock;
                } else {
                    return response()->json(['message' => 'Quantity in stock exceeds total medicine available'], 400);
                }
            }

            if ($request->has('last_restocked')) {
                $stock->last_restocked = $request->last_restocked;
            }

            if ($request->has('expiration_date')) {
                $stock->expiration_date = $request->expiration_date;
            }

            $stock->save();
            return response()->json(['message' => 'Stock updated successfully', 'data' => $stock], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update stock', 'error' => $e->getMessage()], 500);
        }
    }

    // Delete stock
    public function destroy($id)
    {
        try {
            $stock = MedicineStock::findOrFail($id);
            $stock->delete();
            return response()->json(['message' => 'Stock deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Stock not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete stock', 'error' => $e->getMessage()], 500);
        }
    }
}
