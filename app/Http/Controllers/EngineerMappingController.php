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

    public function extra(Request $request)
    {
        if (auth()->user()->role != 1) {
            abort(403, 'Only administrators can access Extra Engineer Mapping.');
        }

        $selectedCategoryId = $request->query('category_id');

        $categories = DB::table('categories')
            ->orderBy('name')
            ->get(['id', 'name', 'assign_role_ids']);

        $engineers = DB::table('users')
            ->where('role', 2)
            ->orWhere('role', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'role_id']);

        $hierarchies = DB::table('category_engineer_hierarchy')
            ->orderBy('category_id')
            ->orderBy('hierarchy')
            ->get()
            ->groupBy('category_id');

        return view('config.extra_engineer_mapping', compact('categories', 'engineers', 'hierarchies', 'selectedCategoryId'));
    }

    public function storeExtra(Request $request)
    {
        if (auth()->user()->role != 1) {
            abort(403, 'Only administrators can access Extra Engineer Mapping.');
        }

        $data = $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'user_ids'      => 'array',
            'user_ids.*'    => 'nullable|exists:users,id',
            'hierarchies'   => 'array',
            'hierarchies.*' => 'nullable|integer|min:1',
        ]);

        $categoryId  = (int) $data['category_id'];
        $userIds     = $data['user_ids'] ?? [];
        $hierarchies = $data['hierarchies'] ?? [];

        $rows = [];
        foreach ($userIds as $index => $userId) {
            $userId     = $userId ?? null;
            $hierarchy  = $hierarchies[$index] ?? null;

            if ($userId && $hierarchy) {
                $rows[] = [
                    'user_id'   => (int) $userId,
                    'hierarchy' => (int) $hierarchy,
                ];
            }
        }

        if (!empty($rows)) {
            usort($rows, function ($a, $b) {
                return $a['hierarchy'] <=> $b['hierarchy'];
            });
        }

        DB::table('category_engineer_hierarchy')
            ->where('category_id', $categoryId)
            ->delete();

        $now = now();
        foreach ($rows as $row) {
            DB::table('category_engineer_hierarchy')->insert([
                'category_id' => $categoryId,
                'user_id'     => $row['user_id'],
                'hierarchy'   => $row['hierarchy'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        return redirect()
            ->route('config.extra_engineer_mapping', ['category_id' => $categoryId])
            ->with('success', 'Extra engineer hierarchy saved.');
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

        return view('config.engineer_mapping_category', compact('category', 'engineers', 'categoryRoleId'));
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
