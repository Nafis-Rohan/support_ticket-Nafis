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

        $categories = DB::table('categories')->orderBy('name')->get(['id', 'name']);

        return view('config.engineer_mapping', compact('categories'));
    }

    public function showCategory($id)
    {
        if (auth()->user()->role != 1) {
            abort(403);
        }

        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            abort(404);
        }

        $assignId = $category->assign_role_ids;
        // assign_role_ids can be "1" or "1,2" – for mapping UI we use the first id
        $categoryRoleId = $this->firstAssignRoleId($assignId);

        // Admin (role=1) + Engineer (role=2)
        $engineers = DB::table('users')
            ->leftJoin('roles', 'users.role', '=', 'roles.id')
            ->whereIn('users.role', [1, 2])
            ->orderBy('roles.id')
            ->orderBy('users.name')
            ->get([
                'users.id',
                'users.name',
                'users.role',
                'users.role_id',
                'roles.name as role_name',
            ]);

        $assignedEngineers = DB::table('category_engineer_map as cem')
            ->join('users as u', 'cem.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'u.role', '=', 'r.id')
            ->where('cem.category_id', (int) $category->id)
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.role',
                'u.role_id',
                'r.name as role_name',
            ]);

        // Available = eligible users (admin/engineer) not already assigned to THIS category
        $availableEngineers = DB::table('users as u')
            ->leftJoin('roles as r', 'u.role', '=', 'r.id')
            ->leftJoin('category_engineer_map as cem', function ($join) use ($id) {
                $join->on('cem.user_id', '=', 'u.id')
                    ->where('cem.category_id', '=', (int) $id);
            })
            ->whereIn('u.role', [1, 2])
            ->whereNull('cem.user_id')
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.role',
                'u.role_id',
                'r.name as role_name',
            ]);

        return view('config.engineer_mapping_category', compact(
            'category',
            'engineers',
            'categoryRoleId',
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

        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category || empty($category->assign_role_ids)) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Category has no assign_role_id set.');
        }

        $assignRoleId = $this->firstAssignRoleId($category->assign_role_ids);
        if ($assignRoleId === null) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Category assign_role_ids is invalid.');
        }

        $user = DB::table('users')->where('id', (int) $request->user_id)->first();
        if (!$user || !in_array((int) $user->role, [1, 2], true)) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Selected user is not eligible.');
        }

        // Don't allow duplicates in same category
        $alreadyInThisCategory = DB::table('category_engineer_map')
            ->where('category_id', (int) $id)
            ->where('user_id', (int) $request->user_id)
            ->exists();
        if ($alreadyInThisCategory) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'This user is already assigned to this category.');
        }

        DB::table('category_engineer_map')->insert([
            'category_id' => (int) $id,
            'user_id'     => (int) $request->user_id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()
            ->route('config.engineer_mapping.category', $id)
            ->with('success', 'Engineer added to this category.');
    }

    public function removeEngineer(Request $request, $id)
    {
        if (auth()->user()->role != 1) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category || empty($category->assign_role_ids)) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Category has no assign_role_id set.');
        }

        $assignRoleId = $this->firstAssignRoleId($category->assign_role_ids);
        if ($assignRoleId === null) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Category assign_role_ids is invalid.');
        }

        $user = DB::table('users')->where('id', (int) $request->user_id)->first();
        if (!$user || !in_array((int) $user->role, [1, 2], true)) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'Selected user is not eligible.');
        }

        $deleted = DB::table('category_engineer_map')
            ->where('category_id', (int) $id)
            ->where('user_id', (int) $request->user_id)
            ->delete();

        if (!$deleted) {
            return redirect()
                ->route('config.engineer_mapping.category', $id)
                ->with('error', 'This user is not assigned to this category.');
        }

        return redirect()
            ->route('config.engineer_mapping.category', $id)
            ->with('success', 'Engineer removed from this category.');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role != 1) {
            abort(403);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $categoryId = (int) $request->category_id;
        $userIds = $request->user_ids ?? [];

        $category = DB::table('categories')
            ->where('id', $categoryId)
            ->first();

        if (!$category || !$category->assign_role_ids) {
            return redirect()
                ->route('config.engineer_mapping.category', $categoryId)
                ->with('error', 'Category has no assign_role_id set.');
        }

        $assignRoleId = $this->firstAssignRoleId($category->assign_role_ids);
        if ($assignRoleId === null) {
            return redirect()
                ->route('config.engineer_mapping.category', $categoryId)
                ->with('error', 'Category assign_role_ids is invalid.');
        }

        // Assign selected (admin + engineer)
        DB::table('users')
            ->whereIn('role', [1, 2])
            ->whereIn('id', $userIds)
            ->update([
                'role_id' => $assignRoleId,
                'updated_at' => now()
            ]);

        // Unassign not selected (admin + engineer)
        DB::table('users')
            ->whereIn('role', [1, 2])
            ->where('role_id', $assignRoleId)
            ->whereNotIn('id', $userIds)
            ->update([
                'role_id' => null,
                'updated_at' => now()
            ]);

        return redirect()
            ->route('config.engineer_mapping.category', $categoryId)
            ->with('success', 'Mapping saved.');
    }

    private function firstAssignRoleId($assignRoleIdsString)
    {
        if (empty($assignRoleIdsString)) {
            return null;
        }
        $parts = array_filter(array_map('trim', explode(',', $assignRoleIdsString)));
        if (empty($parts)) {
            return null;
        }
        return (int) $parts[0];
    }
}
