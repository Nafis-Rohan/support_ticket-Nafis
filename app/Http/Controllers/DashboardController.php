<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->role;
        $userId = auth()->id();

        // Categories shown in the dashboard top cards
        $categoriesQuery = DB::table('categories')->select('id', 'name');

        // Engineers: show only categories that contain mapped sub-categories
        if ($role == 2) {
            $categoriesQuery = DB::table('categories as c')
                ->join('sub_categories as sc', 'sc.category_id', '=', 'c.id')
                ->join('sub_category_engineer_map as sem', 'sem.sub_category_id', '=', 'sc.id')
                ->where('sem.user_id', $userId)
                ->select('c.id', 'c.name')
                ->distinct()
                ->orderBy('c.name');
        }

        $categories = $categoriesQuery->get();

        $tickets = collect();
        $engineerTodayTickets = collect();
        $dashFilterPriority = 'all';
        $dashFilterStatus = 'all';

        if ($role == 2) {
            $dashFilterPriority = $this->normalizeEngineerDashPriority($request->query('priority'));
            $dashFilterStatus = $this->normalizeEngineerDashStatus($request->query('status'));
            $engineerTodayTickets = $this->engineerTodayTicketsForDashboard($userId, $dashFilterPriority, $dashFilterStatus);
        } else {
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

            if ($role == 3) {
                $query->where('t.user_id', auth()->user()->branch_id)
                    ->whereDate('t.created_at', Carbon::today());
            }

            $tickets = $query->get();
        }

        $subCategories = DB::table('sub_categories')->get();

        return view('dashboard.index', compact(
            'categories',
            'tickets',
            'subCategories',
            'engineerTodayTickets',
            'dashFilterPriority',
            'dashFilterStatus'
        ));
    }

    private function normalizeEngineerDashPriority(?string $value): string
    {
        $v = strtolower((string) $value);

        return in_array($v, ['all', 'high', 'urgent', 'medium', 'low', 'unset'], true) ? $v : 'all';
    }

    private function normalizeEngineerDashStatus(?string $value): string
    {
        $v = strtolower((string) $value);

        return in_array($v, ['all', 'pending', 'processing'], true) ? $v : 'all';
    }

    private function engineerTodayTicketsForDashboard(int $userId, string $dashPriority, string $dashStatus)
    {
        $q = DB::table('tickets as t')
            ->leftJoin('categories as c', 't.category_id', '=', 'c.id')
            ->leftJoin('sub_categories as sc', 't.sub_category_id', '=', 'sc.id')
            ->leftJoin('users as s', 't.solved_by', '=', 's.id')
            ->leftJoin('users as a', 't.assigned_to', '=', 'a.id')
            ->leftJoin('priorities as p', 't.priority_id', '=', 'p.id')
            ->select(
                't.*',
                'c.name as category_name',
                'sc.name as sub_category_name',
                's.name as solved_by_name',
                'a.name as assigned_to_name',
                'p.name as priority_name'
            )
            ->where(function ($scope) use ($userId) {
                $scope->where('t.assigned_to', $userId)
                    ->orWhereExists(function ($q2) use ($userId) {
                        $q2->from('sub_category_engineer_map as sem')
                            ->whereColumn('sem.sub_category_id', 't.sub_category_id')
                            ->where('sem.user_id', $userId);
                    });
            })
            ->whereIn('t.status', [0, 1])
            ->whereDate('t.created_at', Carbon::today());

        if ($dashPriority === 'high') {
            $q->where('p.name', 'High');
        } elseif ($dashPriority === 'urgent') {
            $q->where('p.name', 'Urgent');
        } elseif ($dashPriority === 'medium') {
            $q->where('p.name', 'Medium');
        } elseif ($dashPriority === 'low') {
            $q->where('p.name', 'Low');
        } elseif ($dashPriority === 'unset') {
            $q->whereNull('t.priority_id');
        }

        if ($dashStatus === 'pending') {
            $q->where('t.status', 0);
        } elseif ($dashStatus === 'processing') {
            $q->where('t.status', 1);
        }

        return $q->orderByDesc('t.id')->get();
    }

    public function engineerStats()
    {
        if (auth()->user()->role != 2) {
            abort(403, 'Unauthorized');
        }

        $userId = auth()->id();
        $today = Carbon::today();

        $baseQuery = DB::table('tickets as t')
            ->where(function ($scope) use ($userId) {
                $scope->where('t.assigned_to', $userId)
                    ->orWhereExists(function ($q) use ($userId) {
                        $q->from('sub_category_engineer_map as sem')
                            ->whereColumn('sem.sub_category_id', 't.sub_category_id')
                            ->where('sem.user_id', $userId);
                    });
            });

        $pendingProcessingCount = (clone $baseQuery)
            ->whereIn('t.status', [0, 1])
            ->count();

        $todayTicketCount = (clone $baseQuery)
            ->whereDate('t.created_at', $today)
            ->count();

        $todaySolvedCount = DB::table('tickets as t')
            ->where(function ($scope) use ($userId) {
                $scope->where('t.assigned_to', $userId)
                    ->orWhereExists(function ($q) use ($userId) {
                        $q->from('sub_category_engineer_map as sem')
                            ->whereColumn('sem.sub_category_id', 't.sub_category_id')
                            ->where('sem.user_id', $userId);
                    });
            })
            ->where('t.solved_by', $userId)
            ->whereDate('t.updated_at', $today)
            ->count();

        return response()->json([
            'pending_processing_count' => $pendingProcessingCount,
            'today_ticket_count'       => $todayTicketCount,
            'today_solved_count'       => $todaySolvedCount,
        ]);
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
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        return view('dashboard.admin');
    }

    public function adminDashboardData()
    {
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        return response()->json($this->buildAdminDashboardPayload());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAdminDashboardPayload(): array
    {
        $today = Carbon::today();

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

        $totalPending = DB::table('tickets')->where('status', 0)->count();
        $totalProcessing = DB::table('tickets')->where('status', 1)->count();
        $totalSolved = DB::table('tickets')->where('status', 2)->count();
        $totalTickets = $totalPending + $totalProcessing + $totalSolved;

        $topIssuers = DB::table('tickets')
            ->leftJoin('branches as b', 'tickets.user_id', '=', 'b.id')
            ->select('b.name as branch_name', DB::raw('COUNT(tickets.id) as total_tickets'))
            ->groupBy('b.id', 'b.name')
            ->orderByDesc('total_tickets')
            ->limit(10)
            ->get();

        $ticketsByHour = DB::table('tickets')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', $today)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        $statusLabels = ['Pending', 'Processing', 'Solved'];
        $statusCountsToday = [$todayPending, $todayProcessing, $todaySolved];

        $hoursLabels = $ticketsByHour->pluck('hour')->map(function ($h) {
            return sprintf('%02d:00', $h);
        })->values()->all();

        $hoursCounts = $ticketsByHour->pluck('total')->values()->all();

        return [
            'today_label'         => $today->format('d M, Y'),
            'today_total'         => $todayTotal,
            'today_pending'       => $todayPending,
            'today_processing'    => $todayProcessing,
            'today_solved'        => $todaySolved,
            'total_tickets'       => $totalTickets,
            'total_pending'       => $totalPending,
            'total_processing'    => $totalProcessing,
            'total_solved'        => $totalSolved,
            'status_labels'       => $statusLabels,
            'status_counts_today' => $statusCountsToday,
            'hours_labels'        => $hoursLabels,
            'hours_counts'        => $hoursCounts,
            'top_issuers'         => $topIssuers->values()->all(),
        ];
    }
}
