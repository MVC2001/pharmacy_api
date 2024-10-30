<?php

namespace App\Http\Controllers\Api;

use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class MedicineCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = MedicineCategory::all();
            return response()->json([
                'status' => 'success',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch medicine categories',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'medicine_category' => 'required|unique:medicine_categories,medicine_category|max:255',
        ]);

        try {
            $category = MedicineCategory::create([
                'medicine_category' => $request->medicine_category,
            ]);

            Session::flash('message', 'Medicine category created successfully!');

            return response()->json([
                'status' => 'success',
                'message' => 'Medicine category created successfully!',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create medicine category',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = MedicineCategory::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $category
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Medicine category not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'medicine_category' => 'required|max:255|unique:medicine_categories,medicine_category,' . $id . ',medicine_category_id',
        ]);

        try {
            $category = MedicineCategory::findOrFail($id);
            $category->update([
                'medicine_category' => $request->medicine_category,
            ]);

            Session::flash('message', 'Medicine category updated successfully!');

            return response()->json([
                'status' => 'success',
                'message' => 'Medicine category updated successfully!',
                'data' => $category
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update medicine category',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $category = MedicineCategory::findOrFail($id);
            $category->delete();

            Session::flash('message', 'Medicine category deleted successfully!');

            return response()->json([
                'status' => 'success',
                'message' => 'Medicine category deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete medicine category',
            ], 500);
        }
    }
}
