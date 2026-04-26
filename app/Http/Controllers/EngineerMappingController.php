<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EngineerMappingController extends Controller
{
    public function index()
    {
        if (auth()->user()->role != 1) {
            abort(403, 'Only administrators can access Engineer Mapping.');
        }

        $categories = DB::table('categories')
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedCategoryId = (int) request()->query('category_id', 0);
        $search = trim((string) request()->query('search', ''));

        $selectedCategory = null;
        if ($selectedCategoryId > 0) {
            $selectedCategory = $categories->firstWhere('id', $selectedCategoryId);
        }

        $subCategories = DB::table('sub_categories as sc')
            ->leftJoin('categories as c', 'sc.category_id', '=', 'c.id')
            ->when($selectedCategoryId > 0, function ($q) use ($selectedCategoryId) {
                $q->where('sc.category_id', $selectedCategoryId);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where('sc.name', 'like', '%' . $search . '%');
            })
            ->orderBy('sc.name')
            ->get([
                'sc.id',
                'sc.name',
                'sc.category_id',
                'c.name as category_name',
            ]);

        return view('config.engineer_mapping', compact(
            'categories',
            'selectedCategoryId',
            'selectedCategory',
            'search',
            'subCategories'
        ));
    }

    public function showCategory($id)
    {
        if (auth()->user()->role != 1) {
            abort(403);
        }

        $subCategory = DB::table('sub_categories as sc')
            ->leftJoin('categories as c', 'sc.category_id', '=', 'c.id')
            ->where('sc.id', (int) $id)
            ->first([
                'sc.id',
                'sc.name',
                'sc.category_id',
                'c.name as category_name',
            ]);
        if (!$subCategory) {
            abort(404);
        }

        // Engineers only (users.role = 2) for category mapping UI
        $engineers = DB::table('users')
            ->leftJoin('roles', 'users.role', '=', 'roles.id')
            ->where('users.role', 1)
            ->orderBy('users.name')
            ->get([
                'users.id',
                'users.name',
                'users.role',
                'users.role_id',
                'roles.name as role_name',
            ]);

        $assignedEngineers = DB::table('sub_category_engineer_map as sem')
            ->join('users as u', 'sem.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'u.role', '=', 'r.id')
            ->where('sem.sub_category_id', (int) $subCategory->id)
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.role',
                'u.role_id',
                'r.name as role_name',
            ]);

        // Available = engineers (role 2) not already assigned to THIS category
        $availableEngineers = DB::table('users as u')
            ->leftJoin('roles as r', 'u.role', '=', 'r.id')
            ->leftJoin('sub_category_engineer_map as sem', function ($join) use ($id) {
                $join->on('sem.user_id', '=', 'u.id')
                    ->where('sem.sub_category_id', '=', (int) $id);
            })
            ->where('u.role', 1)
            ->whereNull('sem.user_id')
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.role',
                'u.role_id',
                'r.name as role_name',
            ]);

        return view('config.engineer_mapping_category', compact(
            'subCategory',
            'engineers',
            'assignedEngineers',
            'availableEngineers'
        ));
    }

    public function addEngineer(Request $request, $id)
    {
        if (auth()->user()->role != 1) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $subCategory = DB::table('sub_categories')->where('id', (int) $id)->first();
        if (!$subCategory) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Sub-category not found.');
        }

        $user = DB::table('users')->where('id', (int) $request->user_id)->first();
        if (!$user || (int) $user->role !== 1) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Only role=1 users can be mapped here.');
        }

        // Don't allow duplicates in same category
        $alreadyInThisCategory = DB::table('sub_category_engineer_map')
            ->where('sub_category_id', (int) $id)
            ->where('user_id', (int) $request->user_id)
            ->exists();
        if ($alreadyInThisCategory) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'This user is already assigned to this sub-category.');
        }

        DB::table('sub_category_engineer_map')->insert([
            'sub_category_id' => (int) $id,
            'user_id'     => (int) $request->user_id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()
            ->route('config.engineer_mapping.category', $id)
            ->with('success', 'Engineer added to this sub-category.');
    }

    public function removeEngineer(Request $request, $id)
    {
        if (auth()->user()->role != 1) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $subCategory = DB::table('sub_categories')->where('id', (int) $id)->first();
        if (!$subCategory) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Sub-category not found.');
        }

        $inMap = DB::table('sub_category_engineer_map')
            ->where('sub_category_id', (int) $id)
            ->where('user_id', (int) $request->user_id)
            ->exists();
        if (!$inMap) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'This user is not assigned to this sub-category.');
        }

        $deleted = DB::table('sub_category_engineer_map')
            ->where('sub_category_id', (int) $id)
            ->where('user_id', (int) $request->user_id)
            ->delete();

        if (!$deleted) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'This user is not assigned to this sub-category.');
        }

        return redirect()
            ->route('config.engineer_mapping.category', $id)
            ->with('success', 'Engineer removed from this sub-category.');
    }

    public function store(Request $request)
    {
        return back()->with('error', 'Bulk mapping is disabled. Use sub-category add/remove.');
    }
}

