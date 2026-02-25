<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $categories = DB::table('categories')
            ->select('id', 'name')
            ->get();

        // Example assumes these columns exist on tickets table:
        // tickets.category_id, tickets.sub_category_id, tickets.solved_by, tickets.assigned_to
        // and that users table has name
        $query = DB::table('tickets as t')
            ->leftJoin('categories as c', 't.category_id', '=', 'c.id')
            ->leftJoin('sub_categories as sc', 't.sub_category_id', '=', 'sc.id')
            ->leftJoin('users as s', 't.solved_by', '=', 's.id')
            ->leftJoin('users as a', 't.assigned_to', '=', 'a.id')
            ->select(
                't.*',
                'c.name as category_name',
                'sc.name as sub_category_name',
                's.name as solved_by_name',
                'a.name as assigned_to_name'
            )
            ->latest('t.created_at');

        $role = auth()->user()->role;

        // Branch users (role 3): only tickets for their branch
        if ($role == 3) {
            $query->where('t.user_id', auth()->user()->branch_id);
        }

        // Engineers (role 2): only tickets assigned to them
        if ($role == 2) {
            $query->where('t.assigned_to', auth()->id());
        }

        // Admins (role 1): see all tickets (no extra filter)

        $tickets = $query->get();

        $subCategories = DB::table('sub_categories')->get();

        return view('dashboard.index', compact('categories', 'tickets', 'subCategories'));
    }

    public function subCategories($id)
    {
        // Get parent category
        $category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            abort(404);
        }

        // Get all subcategories under this category
        $subCategories = DB::table('sub_categories')
            ->where('category_id', $id)
            ->orderBy('name')
            ->get();

        return view('dashboard.subcategories', compact('category', 'subCategories'));
    }

    public function adminDashboard()
    {
        // Allow only Admin (role == 1)
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        $today = Carbon::today();

        // Today's tickets by status
        $todayPending = DB::table('tickets')
            ->whereDate('created_at', $today)
            ->where('status', 0)
            ->count();

        $todayProcessing = DB::table('tickets')
            ->whereDate('created_at', $today)
            ->where('status', 1)
            ->count();

        $todaySolved = DB::table('tickets')
            ->whereDate('created_at', $today)
            ->where('status', 2)
            ->count();

        $todayTotal = $todayPending + $todayProcessing + $todaySolved;

        // Overall counts (all time)
        $totalPending = DB::table('tickets')->where('status', 0)->count();
        $totalProcessing = DB::table('tickets')->where('status', 1)->count();
        $totalSolved = DB::table('tickets')->where('status', 2)->count();
        $totalTickets = $totalPending + $totalProcessing + $totalSolved;

        // Latest 10 "issuers" (assuming tickets.user_id = branches.id)
        $topIssuers = DB::table('tickets')
            ->leftJoin('branches as b', 'tickets.user_id', '=', 'b.id')
            ->select('b.name as branch_name', DB::raw('COUNT(tickets.id) as total_tickets'))
            ->groupBy('b.id', 'b.name')
            ->orderByDesc('total_tickets')
            ->limit(10)
            ->get();

        // Tickets created per hour today (for a line chart)
        $ticketsByHour = DB::table('tickets')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', $today)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        // Prepare data for charts (arrays so we can encode to JS)
        $statusLabels = ['Pending', 'Processing', 'Solved'];
        $statusCountsToday = [$todayPending, $todayProcessing, $todaySolved];

        $hours = $ticketsByHour->pluck('hour')->map(function ($h) {
            return sprintf('%02d:00', $h);
        });
        $hourCounts = $ticketsByHour->pluck('total');

        return view('dashboard.admin', compact(
            'today',
            'todayTotal',
            'todayPending',
            'todayProcessing',
            'todaySolved',
            'totalTickets',
            'totalPending',
            'totalProcessing',
            'totalSolved',
            'topIssuers',
            'statusLabels',
            'statusCountsToday',
            'hours',
            'hourCounts'
        ));
    }
}
