<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{



    // Show all categories
    public function index()
    {

        $categories = DB::table('categories')->orderBy('id', 'ASC')->get();
        return view('config.categories', compact('categories'));
    }

    // Manage Sub Categories – current sub categories always shown
    public function subCategoriesIndex()
    {
        $categories = DB::table('categories')
            ->orderBy('name', 'ASC') // Recommended: Sort dropdowns by Name
            ->get();

        $subCategories = DB::table('sub_categories as sc')
            ->leftJoin('categories as c', 'sc.category_id', '=', 'c.id')
            ->select('sc.id', 'sc.name as sub_category_name', 'sc.category_id', 'c.name as category_name')
            ->orderBy('sc.id', 'desc') //decending

            ->paginate(10);
        return view('config.sub_categories', compact('categories', 'subCategories'));
    }

    // Store new sub category
    public function storeSubCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);
        DB::table('sub_categories')->insert([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('config.sub_categories')->with('success', 'Sub category added successfully!');
    }

    // Show edit form for sub category
    public function editSubCategory($id)
    {
        $subCategory = DB::table('sub_categories')->where('id', $id)->first();
        if (!$subCategory) {
            abort(404);
        }
        $categories = DB::table('categories')->orderBy('id', 'ASC')->get();
        return view('config.sub_categories_edit', compact('subCategory', 'categories'));
    }

    // Update sub category
    public function updateSubCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);
        DB::table('sub_categories')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'updated_at' => now(),
            ]);
        return redirect()->route('config.sub_categories')->with('success', 'Sub category updated successfully!');
    }

    // Delete sub category
    public function destroySubCategory($id)
    {
        DB::table('sub_categories')->where('id', $id)->delete();
        return redirect()->route('config.sub_categories')->with('success', 'Sub category deleted successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            abort(404);
        }
        return view('config.categories_edit', compact('category'));
    }

    // Store new category
    public function store(Request $request)
    {

        DB::table('categories')->insert([
            'name' => $request->category_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category added successfully!');
    }

    // Update category (example: toggle status)
    public function update(Request $request, $id)
    {
        DB::table('categories')
            ->where('id', $id)
            ->update([
                'name' => $request->category_name,
                'updated_at' => now(),
            ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    // Delete category
    public function destroy($id)
    {
        DB::table('categories')->where('id', $id)->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
