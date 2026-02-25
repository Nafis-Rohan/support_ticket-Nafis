<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $query = DB::table('tickets')
            ->leftJoin('branches as b', 'tickets.user_id', '=', 'b.id') // user_id = branch_id
            ->leftJoin('users as solvers', 'tickets.solved_by', '=', 'solvers.id')
            ->leftJoin('users as assigned', 'tickets.assigned_to', '=', 'assigned.id')
            ->leftJoin('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'tickets.sub_category_id', '=', 'sub_categories.id')
            ->select(
                'tickets.*',
                'b.name as br_name', // branch name
                'solvers.name as solved_by_name',
                'assigned.name as assigned_to_name',
                'priorities.name as priority_name',
                'categories.name as category_name',
                'sub_categories.name as sub_category_name'
            )
            ->orderBy('tickets.created_at', 'desc');

        $role = auth()->user()->role;

        // Branch users (role 3): only their branch tickets
        if ($role == 3) {
            $query->where('tickets.user_id', auth()->user()->branch_id);
        }

        // Engineer (role 2): only tickets assigned to them
        if ($role == 2) {
            $query->where('tickets.assigned_to', auth()->id());
        }

        // Admin (role 1): no filter, sees all

        $tickets = $query->get();
        $tickets->transform(function ($t) {
            $t->assigned_engineers = DB::table('ticket_engineer as te')
                ->join('users as u', 'te.user_id', '=', 'u.id')
                ->where('te.ticket_id', $t->id)
                ->pluck('u.name')
                ->toArray();
            return $t;
        });

        return view('tickets.index', compact('tickets'));
    }

    public function getSubCategories($categoryId)
    {
        $subCategories = DB::table('sub_categories')
            ->where('category_id', $categoryId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subCategories);
    }


    public function create(Request $request)
    {
        $branches = DB::table('branches')->select('id', 'name')->get();
        $priorities = DB::table('priorities')->select('id', 'name')->get();
        $categories = DB::table('categories')->select('id', 'name')->get();

        $selectedCategory = $request->category; // get category from query string
        $selectedSubCategory = $request->sub_category; // sub-cat

        return view('tickets.create', compact('branches', 'priorities', 'categories', 'selectedCategory', 'selectedSubCategory'));
    }

    public function store(Request $request)
    { {
            $request->validate([
                'subject'         => 'required|string|max:255',
                'description'     => 'required|string',
                'contact_person'  => 'nullable|string|max:255',
                'attachment'      => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx|max:2048',
                'priority_id'     => 'nullable|exists:priorities,id',
                'category_id'     => 'nullable|exists:categories,id',
                'sub_category_id' => 'nullable|exists:sub_categories,id',
                'branch_id'       => 'nullable|integer|exists:branches,id',
            ]);

            $filePath = null;
            if ($request->hasFile('attachment')) {
                $filePath = $request->file('attachment')->store('tickets', 'public');
            }

            // ✅ ALWAYS define these so both admin & branch paths can use them
            $assignedTo  = null;
            $engineerIds = [];
            $ticketId    = null;

            // ✅ Determine which branch_id will be stored in tickets.user_id
            if (auth()->user()->role === 1) {
                $branchId = $request->branch_id;

                $user = DB::table('users')
                    ->where('branch_id', $branchId)
                    ->first();

                if (!$user) {
                    return back()->with('error', 'No user found in the selected branch.');
                }
            } else {
                $branchId = auth()->user()->branch_id;
            }

            // ✅ AUTO-ASSIGN ENGINEERS (by role_id: category.assign_role_ids ↔ user.role_id)
            if ($request->category_id) {
                $categoryId = (int) $request->category_id;
                $assignRoleIdsString = DB::table('categories')
                    ->where('id', $categoryId)
                    ->value('assign_role_ids');
                if (!empty($assignRoleIdsString)) {
                    $roleIds = array_filter(array_map('trim', explode(',', $assignRoleIdsString)));
                    if (!empty($roleIds)) {
                        // role 2 = Engineer, role 1 = Admin
                        // Both can be auto-assigned if their role_id matches assign_role_ids
                        $engineers = DB::table('users')
                            ->whereIn('role', [1, 2])
                            ->whereIn('role_id', $roleIds)
                            ->get();
                        if ($engineers->isNotEmpty()) {
                            $assignedTo  = $engineers->first()->id;
                            $engineerIds = $engineers->pluck('id')->all();
                        }
                    }
                }
            }

            // ✅ Insert ticket and get ticket id (IMPORTANT)
            $ticketId = DB::table('tickets')->insertGetId([
                'user_id'         => $branchId,
                'subject'         => $request->subject,
                'description'     => $request->description,
                'contact_person'  => $request->contact_person,
                'attachment'      => $filePath,
                'priority_id'     => $request->priority_id,
                'category_id'     => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'assigned_to'     => $assignedTo,   // can be null
                'status'          => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // ✅ Save all engineers in ticket_engineer
            if (!empty($engineerIds)) {
                $now = now();
                $rows = [];

                foreach ($engineerIds as $engId) {
                    $rows[] = [
                        'ticket_id'  => $ticketId,
                        'user_id'    => $engId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('ticket_engineer')->insert($rows);
            }

            return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
        }
    }
    public function show($id)
    {
        $ticket = DB::table('tickets')
            ->leftJoin('users as solvers', 'tickets.solved_by', '=', 'solvers.id')
            ->leftJoin('users as assigned', 'tickets.assigned_to', '=', 'assigned.id') //assigned engineer
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->leftJoin('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->leftJoin('sub_categories', 'tickets.sub_category_id', '=', 'sub_categories.id')
            ->select(
                'tickets.*',
                'solvers.name as solved_by_name',
                'assigned.name as assigned_to_name',      //engineer name
                'categories.name as category_name',
                'sub_categories.name as sub_category_name',
                'priorities.name as priority_name'
            )
            ->where('tickets.id', $id)
            ->first();

        if (!$ticket) {
            abort(404);
        }

        // Replies
        $replies = DB::table('ticket_replies')
            ->join('users', 'ticket_replies.user_id', '=', 'users.id')
            ->where('ticket_replies.ticket_id', $id)
            ->orderBy('ticket_replies.created_at', 'asc')
            ->select(
                'ticket_replies.*',
                'users.name as user_name'
            )
            ->get();

        // Engineers list for assigning (role = 2)
        $engineers = DB::table('users')
            ->where('role', 2)
            ->select('id', 'name')
            ->get();

        $assignedEngineers = DB::table('ticket_engineer as te')
            ->join('users as u', 'te.user_id', '=', 'u.id')
            ->where('te.ticket_id', $id)
            ->orderBy('u.name')
            ->select('u.id', 'u.name')
            ->get();


        $logRows = DB::table('ticket_status_logs')
            ->where('ticket_id', $id)
            ->orderBy('created_at', 'asc')
            ->get(['status', 'created_at']);

        $pendingAt    = \Carbon\Carbon::parse($ticket->created_at);
        $processingAt = $ticket->status >= 1 ? \Carbon\Carbon::parse($ticket->updated_at) : null;
        $solvedAt     = $ticket->status == 2 ? \Carbon\Carbon::parse($ticket->updated_at) : null;


        return view('tickets.show', compact('ticket', 'replies', 'engineers', 'pendingAt', 'processingAt', 'solvedAt', 'assignedEngineers'));
    }

    public function storeReply(Request $request, $id)
    {
        // Allow only Admin (1) and Engineer (2)
        if (!in_array(auth()->user()->role, [1, 2])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        DB::table('ticket_replies')->insert([
            'ticket_id'  => $id,
            'user_id'    => auth()->id(),
            'message'    => $request->message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('tickets.show', $id)
            ->with('success', 'Reply added successfully.');
    }


    public function update(Request $request, $id)
    {
        // Only Admin and Engineer can update status
        if (!in_array(auth()->user()->role, [1, 2])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        // Get the ticket so we can check assigned_to
        $ticket = DB::table('tickets')->where('id', $id)->first();

        if (!$ticket) {
            abort(404);
        }

        $solvedBy = null;

        // If ticket is being marked as Solved
        if ((int) $request->status === 2) {
            if (!empty($ticket->assigned_to)) {
                // ✅ If assigned to an engineer, mark that engineer as solver
                $solvedBy = $ticket->assigned_to;
            } else {
                // ✅ If no assigned engineer, fallback to whoever clicked Solved
                $solvedBy = auth()->id();
            }
        }

        DB::table('tickets')
            ->where('id', $id)
            ->update([
                'status'     => $request->status,
                'solved_by'  => $solvedBy,
                'updated_at' => now(),
            ]);

        // Log status change if changed

        if ((int)$ticket->status !== (int)$request->status) {
            DB::table('ticket_status_logs')->insert([
                'ticket_id'  => $id,
                'status'     => (int)$request->status,
                'changed_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        return redirect()
            ->route('tickets.index', $id)
            ->with('success', 'Ticket status updated successfully.');
    }

    public function assignEngineer(Request $request, $id)
    {
        // Only Admin can assign
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'engineer_id' => 'required|exists:users,id',
            'note'        => 'nullable|string',
        ]);

        // Ensure the selected user is actually an Engineer (role 2)
        $engineer = DB::table('users')
            ->where('id', $request->engineer_id)
            ->where('role', 2) // role 2 = Engineer
            ->first();

        if (!$engineer) {
            return back()->with('error', 'Selected user is not an Engineer.');
        }

        // 1) Update ticket assignment
        DB::table('tickets')
            ->where('id', $id)
            ->update([
                'assigned_to' => $engineer->id,
                'updated_at'  => now(),
            ]);

        // If a note is provided, insert it into ticket_replies.note
        if (!empty(trim($request->note))) {
            DB::table('ticket_replies')->insert([
                'ticket_id'  => $id,
                'user_id'    => auth()->id(),
                'note'       => $request->note,  // insert raw text into `note` column
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('tickets.show', $id)
            ->with('success', 'Ticket assigned to Engineer successfully.');
    }
}
