<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function branchReport(Request $request)
    {
        $tickets = [];

        if ($request->filled(['from_date', 'to_date'])) {
            $tickets = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('branches', 'users.branch_id', '=', 'branches.id')
                ->select('tickets.*', 'branches.name as branch_name')
                ->whereBetween('tickets.created_at', [$request->from_date, $request->to_date])
                ->orderBy('tickets.created_at', 'desc')
                ->get();
        }

        return view('reports.branch', compact('tickets'));
    }

    public function problemReport(Request $request)
    {
        // Admin only
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        $categories = DB::table('categories')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $selectedCategoryId = (int) $request->get('category_id', 0);
        $selectedSubCategoryId = (int) $request->get('sub_category_id', 0);

        $subCategories = collect();
        if ($selectedCategoryId) {
            $subCategories = DB::table('sub_categories')
                ->where('category_id', $selectedCategoryId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        $tickets = collect();
        if ($selectedCategoryId) {
            $query = DB::table('tickets as t')
                ->leftJoin('branches as b', 't.user_id', '=', 'b.id')
                ->leftJoin('categories as c', 't.category_id', '=', 'c.id')
                ->leftJoin('sub_categories as sc', 't.sub_category_id', '=', 'sc.id')
                ->leftJoin('users as s', 't.solved_by', '=', 's.id')
                ->leftJoin('users as a', 't.assigned_to', '=', 'a.id')
                ->leftJoin('priorities as p', 't.priority_id', '=', 'p.id')
                ->select(
                    't.*',
                    'b.name as branch_name',
                    'c.name as category_name',
                    'sc.name as sub_category_name',
                    's.name as solved_by_name',
                    'a.name as assigned_to_name',
                    'p.name as priority_name'
                )
                ->where('t.category_id', $selectedCategoryId);

            if ($selectedSubCategoryId) {
                $query->where('t.sub_category_id', $selectedSubCategoryId);
            }

            $tickets = $query->orderByDesc('t.id')->get();
        }

        return view('reports.problem', compact(
            'categories',
            'subCategories',
            'selectedCategoryId',
            'selectedSubCategoryId',
            'tickets'
        ));
    }
}
