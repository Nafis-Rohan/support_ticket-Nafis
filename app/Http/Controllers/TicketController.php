<?php

namespace App\Http\Controllers;

use App\Support\DisplayTime;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class TicketController extends Controller
{
    private function isUserMappedToSubCategory(int $userId, $subCategoryId): bool
    {
        $subCategoryId = (int) $subCategoryId;
        if ($subCategoryId <= 0) {
            return false;
        }

        return DB::table('sub_category_engineer_map')
            ->where('sub_category_id', $subCategoryId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * @return 'all'|'today'
     */
    private function openUnsolvedFilter(Request $request): string
    {
        $f = $request->query('filter', 'today');

        return $f === 'all' ? 'all' : 'today';
    }

    public function index(Request $request)
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

        $branchTicketFilter = 'solved';

        // Branch users (role 3): only their branch tickets
        if ($role == 3) {
            $query->where('tickets.user_id', auth()->user()->branch_id);

            $branchTicketFilterRaw = strtolower((string) $request->query('status', 'solved'));
            $branchTicketFilter = in_array($branchTicketFilterRaw, ['solved', 'unsolved', 'all'], true)
                ? $branchTicketFilterRaw
                : 'solved';

            if ($branchTicketFilter === 'solved') {
                $query->where('tickets.status', 2);
            } elseif ($branchTicketFilter === 'unsolved') {
                $query->whereIn('tickets.status', [0, 1]);
            }
        }

        // Engineer (role 2): show tickets from mapped sub-categories OR explicitly assigned tickets
        if ($role == 2) {
            $userId = auth()->id();
            $query->where(function ($scope) use ($userId) {
                $scope->where('tickets.assigned_to', $userId)
                    ->orWhereExists(function ($q) use ($userId) {
                        $q->from('sub_category_engineer_map as sem')
                            ->whereColumn('sem.sub_category_id', 'tickets.sub_category_id')
                            ->where('sem.user_id', $userId);
                    });
            });

            // Engineer tickets page: only tickets solved by this engineer
            $query->where('tickets.status', 2)
                ->where('tickets.solved_by', $userId);
        }

        // Admin (role 1): show only solved tickets from sub-categories mapped to this admin
        if ($role == 1) {
            $query->where('tickets.status', 2)
                ->whereExists(function ($q) {
                    $q->from('sub_category_engineer_map as sem')
                        ->whereColumn('sem.sub_category_id', 'tickets.sub_category_id')
                        ->where('sem.user_id', auth()->id());
                });
        }

        $tickets = $query->get();

        $pageTitle = ($role == 3 && $branchTicketFilter === 'unsolved') ? 'Unsolved Tickets' : 'Solved Tickets';
        return view('tickets.index', compact('tickets', 'pageTitle', 'branchTicketFilter'));
    }

    public function openTickets(Request $request)
    {
        // Admin-only list for Pending + Processing
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        $openTicketFilter = $this->openUnsolvedFilter($request);

        $query = DB::table('tickets')
            ->leftJoin('branches as b', 'tickets.user_id', '=', 'b.id')
            ->leftJoin('users as solvers', 'tickets.solved_by', '=', 'solvers.id')
            ->leftJoin('users as assigned', 'tickets.assigned_to', '=', 'assigned.id')
            ->leftJoin('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'tickets.sub_category_id', '=', 'sub_categories.id')
            ->select(
                'tickets.*',
                'b.name as br_name',
                'solvers.name as solved_by_name',
                'assigned.name as assigned_to_name',
                'priorities.name as priority_name',
                'categories.name as category_name',
                'sub_categories.name as sub_category_name'
            )
            ->whereIn('tickets.status', [0, 1]);

        // Admin can view only sub-categories mapped to this admin
        $query->whereExists(function ($q) {
            $q->from('sub_category_engineer_map as sem')
                ->whereColumn('sem.sub_category_id', 'tickets.sub_category_id')
                ->where('sem.user_id', auth()->id());
        });

        if ($openTicketFilter === 'today') {
            $query->whereDate('tickets.created_at', \Carbon\Carbon::today());
        }

        $tickets = $query->orderByDesc('tickets.id')->get();

        $pageTitle = 'Tickets';
        return view('tickets.index', compact('tickets', 'pageTitle', 'openTicketFilter'));
    }

    public function engineerOpenTickets(Request $request)
    {
        // Engineer-only list for Pending + Processing
        if (auth()->user()->role != 2) {
            abort(403, 'Unauthorized');
        }

        $openTicketFilter = $this->openUnsolvedFilter($request);
        $userId = auth()->id();

        $query = DB::table('tickets')
            ->leftJoin('branches as b', 'tickets.user_id', '=', 'b.id')
            ->leftJoin('users as solvers', 'tickets.solved_by', '=', 'solvers.id')
            ->leftJoin('users as assigned', 'tickets.assigned_to', '=', 'assigned.id')
            ->leftJoin('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'tickets.sub_category_id', '=', 'sub_categories.id')
            ->select(
                'tickets.*',
                'b.name as br_name',
                'solvers.name as solved_by_name',
                'assigned.name as assigned_to_name',
                'priorities.name as priority_name',
                'categories.name as category_name',
                'sub_categories.name as sub_category_name'
            )
            ->where(function ($scope) use ($userId) {
                $scope->where('tickets.assigned_to', $userId)
                    ->orWhereExists(function ($q) use ($userId) {
                        $q->from('sub_category_engineer_map as sem')
                            ->whereColumn('sem.sub_category_id', 'tickets.sub_category_id')
                            ->where('sem.user_id', $userId);
                    });
            })
            ->whereIn('tickets.status', [0, 1]);

        if ($openTicketFilter === 'today') {
            $query->whereDate('tickets.created_at', \Carbon\Carbon::today());
        }

        $tickets = $query->orderByDesc('tickets.id')->get();

        $pageTitle = 'Tickets';
        return view('tickets.index', compact('tickets', 'pageTitle', 'openTicketFilter'));
    }

    public function byCategory(Request $request)
    {
        // Only Engineers should use this page
        if (auth()->user()->role != 2) {
            abort(403, 'Unauthorized');
        }

        $userId = auth()->id();

        // Categories where this engineer has mapped sub-categories
        $categories = DB::table('categories as c')
            ->join('sub_categories as sc', 'sc.category_id', '=', 'c.id')
            ->join('sub_category_engineer_map as sem', 'sem.sub_category_id', '=', 'sc.id')
            ->where('sem.user_id', $userId)
            ->select('c.id', 'c.name')
            ->distinct()
            ->orderBy('c.name')
            ->get();

        $selectedCategoryId = (int) $request->get('category_id', 0);

        $tickets = collect();

        if ($selectedCategoryId) {
            $tickets = DB::table('tickets')
                ->leftJoin('branches as b', 'tickets.user_id', '=', 'b.id')
                ->leftJoin('users as solvers', 'tickets.solved_by', '=', 'solvers.id')
                ->leftJoin('users as assigned', 'tickets.assigned_to', '=', 'assigned.id')
                ->leftJoin('priorities', 'tickets.priority_id', '=', 'priorities.id')
                ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
                ->leftJoin('sub_categories', 'tickets.sub_category_id', '=', 'sub_categories.id')
                ->select(
                    'tickets.*',
                    'b.name as br_name',
                    'solvers.name as solved_by_name',
                    'assigned.name as assigned_to_name',
                    'priorities.name as priority_name',
                    'categories.name as category_name',
                    'sub_categories.name as sub_category_name'
                )
                ->where('tickets.category_id', $selectedCategoryId)
                ->whereExists(function ($q) use ($userId) {
                    $q->from('sub_category_engineer_map as sem')
                        ->whereColumn('sem.sub_category_id', 'tickets.sub_category_id')
                        ->where('sem.user_id', $userId);
                })
                ->orderBy('tickets.created_at', 'desc')
                ->get();
        }

        return view('tickets.category_index', compact('categories', 'selectedCategoryId', 'tickets'));
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

            // attachments[] can arrive as a single UploadedFile, an array, or via allFiles() — normalize.
            $attachmentRows = [];
            $uploadedFiles = $request->file('attachments');
            if ($uploadedFiles === null && isset($request->allFiles()['attachments'])) {
                $uploadedFiles = $request->allFiles()['attachments'];
            }
            $uploadedFiles = array_filter(
                Arr::wrap($uploadedFiles),
                function ($f) {
                    return $f instanceof UploadedFile;
                }
            );
            foreach ($uploadedFiles as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                Validator::make(
                    ['_f' => $file],
                    ['_f' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx|max:2048']
                )->validate();

                $storedPath = $file->store('tickets', 'public');
                $attachmentRows[] = [
                    'file_path'     => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                ];
            }

            // ✅ ALWAYS define these so both admin & branch paths can use them
            $assignedTo        = null; // keep null: no auto-assign
            $engineerIds       = [];
            $ticketId          = null;

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

            // ✅ Determine which engineers can see this ticket (sub-category mapping)
            if ($request->sub_category_id) {
                $subCategoryId = (int) $request->sub_category_id;

                // Prefer explicit per-sub-category mapping table
                $mappedEngineerIds = DB::table('sub_category_engineer_map as sem')
                    ->join('users as u', 'sem.user_id', '=', 'u.id')
                    ->where('sem.sub_category_id', $subCategoryId)
                    ->where('u.role', 2)
                    ->pluck('u.id')
                    ->all();

                if (!empty($mappedEngineerIds)) {
                    $engineerIds = $mappedEngineerIds;
                } else {
                    // Fallback: old assign_role_ids -> users.role_id mapping (engineers only)
                    $categoryId = (int) ($request->category_id ?? 0);
                    $assignRoleIdsString = DB::table('categories')
                        ->where('id', $categoryId)
                        ->value('assign_role_ids');
                    if (!empty($assignRoleIdsString)) {
                        $roleIds = array_filter(array_map('trim', explode(',', $assignRoleIdsString)));
                        if (!empty($roleIds)) {
                            $engineerIds = DB::table('users')
                                ->where('role', 2)
                                ->whereIn('role_id', $roleIds)
                                ->pluck('id')
                                ->all();
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
                'assigned_to'     => null,   // no auto-assign
                'assigned_hierarchy' => null,
                'status'          => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            if (!empty($attachmentRows)) {
                $now = now();
                foreach ($attachmentRows as $row) {
                    DB::table('ticket_attachments')->insert([
                        'ticket_id'      => $ticketId,
                        'file_path'      => $row['file_path'],
                        'original_name'  => $row['original_name'],
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
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

        // Admin can open ticket details only for mapped sub-categories.
        if ((int) (auth()->user()->role ?? 0) === 1) {
            $canView = $this->isUserMappedToSubCategory((int) auth()->id(), $ticket->sub_category_id ?? null);
            if (!$canView) {
                abort(403, 'You are not mapped for this ticket sub-category.');
            }
        }

        // Manual Assign options: allow both Admin (role=1) and Engineer (role=2)
        $manualAssignEngineers = DB::table('users')
            ->whereIn('role', [1, 2])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        // Sub-category-mapped engineers only (for Forward / rotation); excludes admins mistakenly in map
        $categoryEngineers = collect();
        $categoryMappedEngineers = collect();
        $isEngineerForCategory = false;
        $nextEngineer = null;
        if (!empty($ticket->sub_category_id)) {
            $categoryEngineers = DB::table('sub_category_engineer_map as sem')
                ->join('users as u', 'sem.user_id', '=', 'u.id')
                ->where('sem.sub_category_id', (int) $ticket->sub_category_id)
                ->where('u.role', 2)
                ->orderBy('u.name')
                ->get(['u.id', 'u.name']);

            $categoryMappedEngineers = $categoryEngineers;

            // Next engineer for the "Forward to Next Engineer" button.
            // We rotate inside the mapped engineer list for this ticket's category.
            if ($categoryMappedEngineers->count() > 0) {
                $engineerList = $categoryMappedEngineers->values()->all(); // array of objects {id,name}
                $assignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;

                $currentIndex = null;
                if ($assignedToId !== null) {
                    foreach ($engineerList as $i => $eng) {
                        if ((int) $eng->id === $assignedToId) {
                            $currentIndex = $i;
                            break;
                        }
                    }
                }

                if ($currentIndex === null) {
                    $nextEngineer = $engineerList[0] ?? null;
                } else {
                    $nextIndex = ($currentIndex + 1) % count($engineerList);
                    $nextEngineer = $engineerList[$nextIndex] ?? null;
                }
            }

            if (auth()->check() && in_array(auth()->user()->role, [1, 2])) {
                $isEngineerForCategory = DB::table('sub_category_engineer_map')
                    ->where('sub_category_id', (int) $ticket->sub_category_id)
                    ->where('user_id', (int) auth()->id())
                    ->exists();
            }
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

        // Developer Forward dropdown (role 2 only); used on ticket show for engineers
        $forwardDeveloperOptions = DB::table('users')
            ->where('role', 2)
            ->orderBy('name')
            ->get(['id', 'name']);

        $assignedEngineers = collect();
        if (!empty($ticket->assigned_to)) {
            $u = DB::table('users')->where('id', $ticket->assigned_to)->first(['id', 'name']);
            if ($u) {
                $assignedEngineers = collect([(object) ['id' => $u->id, 'name' => $u->name]]);
            }
        }


        $logRows = DB::table('ticket_status_logs')
            ->where('ticket_id', $id)
            ->orderBy('created_at', 'asc')
            ->get(['status', 'created_at']);

        // Use status logs when available so timeline timestamps reflect:
        // - Pending = ticket created_at
        // - Processing = when status first became 1
        // - Solved = when status first became 2
        $pendingAt = DisplayTime::fromUtcStored($ticket->created_at ?? null);

        $processingAt = null;
        $solvedAt = null;
        foreach ($logRows as $row) {
            $s = (int) ($row->status ?? 0);
            $t = DisplayTime::fromUtcStored($row->created_at ?? null);
            if (!$t) {
                continue;
            }
            if ($s === 1 && $processingAt === null) {
                $processingAt = $t;
            }
            if ($s === 2 && $solvedAt === null) {
                $solvedAt = $t;
            }
        }

        // Fallbacks for older tickets where logs might be missing
        if ($processingAt === null && (int) $ticket->status >= 1) {
            $processingAt = DisplayTime::fromUtcStored($ticket->updated_at ?? null);
        }
        if ($solvedAt === null && (int) $ticket->status === 2) {
            $solvedAt = DisplayTime::fromUtcStored($ticket->updated_at ?? null);
        }

        $priorities = collect();
        if (auth()->check() && auth()->user()->role === 1) {
            $priorities = DB::table('priorities')->select('id', 'name')->orderBy('name')->get();
        }

        $attachments = DB::table('ticket_attachments')
            ->where('ticket_id', $id)
            ->orderBy('id')
            ->get(['file_path', 'original_name']);

        return view('tickets.show', compact(
            'ticket',
            'replies',
            'engineers',
            'pendingAt',
            'processingAt',
            'solvedAt',
            'assignedEngineers',
            'categoryEngineers',
            'categoryMappedEngineers',
            'manualAssignEngineers',
            'isEngineerForCategory',
            'nextEngineer',
            'priorities',
            'attachments',
            'forwardDeveloperOptions'
        ));
    }

    public function updatePriority(Request $request, $id)
    {
        if (auth()->user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'priority_id' => 'nullable|exists:priorities,id',
        ]);

        $ticket = DB::table('tickets')->where('id', $id)->first();
        if (!$ticket) {
            abort(404);
        }

        if ((int) $ticket->status === 2) {
            return back()->with('error', 'Priority cannot be changed for a solved ticket.');
        }

        $priorityId = $request->filled('priority_id') ? (int) $request->priority_id : null;

        DB::table('tickets')
            ->where('id', $id)
            ->update([
                'priority_id' => $priorityId,
                'updated_at'  => now(),
            ]);

        return back()->with('success', 'Priority updated successfully.');
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
            'status'         => 'required|in:0,1,2',
            'solved_message' => 'nullable|string|max:2000',
        ]);

        // Get the ticket so we can check assigned_to
        $ticket = DB::table('tickets')->where('id', $id)->first();

        if (!$ticket) {
            abort(404);
        }

        $solvedBy = null;
        $solvedMessage = null;

        // If ticket is being marked as Solved, only the attending user can do it
        if ((int) $request->status === 2) {
            if (empty($ticket->assigned_to) || (int) $ticket->assigned_to !== (int) auth()->id()) {
                return back()->with('error', 'Only the user currently attending this ticket can mark it as solved.');
            }

            if (!empty($ticket->assigned_to)) {
                $solvedBy = $ticket->assigned_to;
            } else {
                $solvedBy = auth()->id();
            }
            $solvedMessage = $request->filled('solved_message') ? trim($request->solved_message) : null;
        }

        $update = [
            'status'     => $request->status,
            'solved_by'  => $solvedBy,
            'updated_at' => now(),
        ];
        if ((int) $request->status === 2) {
            $update['solved_message'] = $solvedMessage;
        }

        DB::table('tickets')
            ->where('id', $id)
            ->update($update);

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

        $ticket = DB::table('tickets')->where('id', $id)->first();
        if (!$ticket) {
            abort(404);
        }
        if (empty($ticket->sub_category_id)) {
            return back()->with('error', 'This ticket has no sub-category; assign an engineer after setting a sub-category.');
        }
        if ((int) $ticket->status === 2) {
            return back()->with('error', 'Cannot reassign a solved ticket.');
        }

        $previousStatus = (int) ($ticket->status ?? 0);

        // Ensure the selected user is eligible (Admin=1 or Engineer=2)
        $engineer = DB::table('users')
            ->where('id', $request->engineer_id)
            ->whereIn('role', [1, 2])
            ->first();

        if (!$engineer) {
            return back()->with('error', 'Selected user is not eligible for assignment.');
        }

        $noteText = trim((string) ($request->note ?? ''));
        $handoffNote = $noteText !== '' ? $noteText : null;

        DB::table('tickets')
            ->where('id', $id)
            ->update([
                'assigned_to'  => $engineer->id,
                'handoff_note' => $handoffNote,
                // When admin manually assigns an engineer, the ticket should move to Processing.
                'status'        => 1, // Processing
                'updated_at'   => now(),
            ]);

        // Record timeline timestamp for "Processing" at manual assignment time.
        if ($previousStatus !== 1) {
            DB::table('ticket_status_logs')->insert([
                'ticket_id'  => $id,
                'status'     => 1,
                'changed_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        if ($handoffNote !== null) {
            DB::table('ticket_replies')->insert([
                'ticket_id'  => $id,
                'user_id'    => auth()->id(),
                'message'    => 'Manual assign to ' . ($engineer->name ?? ('User #' . $engineer->id)) . ': ' . $handoffNote,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('tickets.show', $id)
            ->with('success', 'Ticket assigned to Engineer successfully.');
    }

    public function takeAction(Request $request, $id)
    {
        // Only Admin and Engineer can take action.
        if (!in_array(auth()->user()->role, [1, 2])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'action'     => 'required|in:attend,forward',
            'forward_to' => 'nullable|exists:users,id',
            'note'       => 'nullable|string',
        ]);

        $ticket = DB::table('tickets')->where('id', $id)->first();
        if (!$ticket) {
            abort(404);
        }

        if (empty($ticket->sub_category_id)) {
            return back()->with('error', 'Ticket has no sub-category.');
        }

        $role = (int) (auth()->user()->role ?? 0);

        // Engineers must be mapped to the ticket sub-category; admin can attend directly.
        if ($role === 2) {
            $canWork = DB::table('sub_category_engineer_map')
                ->where('sub_category_id', (int) $ticket->sub_category_id)
                ->where('user_id', (int) auth()->id())
                ->exists();
            $isAssignedToSelf = (int) ($ticket->assigned_to ?? 0) === (int) auth()->id();
            if (!$canWork && !$isAssignedToSelf) {
                abort(403, 'You are not assigned for this sub-category.');
            }
        }

        if ($request->action === 'attend') {
            if (!empty($ticket->assigned_to) && (int) $ticket->assigned_to !== (int) auth()->id()) {
                return back()->with('error', 'Another engineer is already working on this ticket.');
            }

            $previousStatus = (int) ($ticket->status ?? 0);
            DB::table('tickets')
                ->where('id', $id)
                ->update([
                    'assigned_to' => auth()->id(),
                    'status'      => 1, // Processing
                    'updated_at'  => now(),
                ]);

            // Record timeline timestamp for "Processing" when first attended.
            if ($previousStatus !== 1) {
                DB::table('ticket_status_logs')->insert([
                    'ticket_id'  => $id,
                    'status'     => 1,
                    'changed_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return redirect()
                ->route('tickets.show', $id)
                ->with('success', 'You are now attending this ticket. Status set to Processing.');
        }

        // Forward: only current attending engineer can forward
        if ((int) $ticket->assigned_to !== (int) auth()->id()) {
            return back()->with('error', 'Only the attending engineer can forward this ticket.');
        }

        $forwardTo = (int) $request->forward_to;
        if (!$forwardTo) {
            return back()->with('error', 'Please select an engineer to forward to.');
        }

        $forwardTarget = DB::table('users')->where('id', $forwardTo)->first();
        if (!$forwardTarget || (int) $forwardTarget->role !== 2) {
            return back()->with('error', 'You can only forward to a developer account.');
        }
        if ($forwardTo === (int) auth()->id()) {
            return back()->with('error', 'Cannot forward to yourself.');
        }

        $note = trim((string) ($request->note ?? ''));
        $handoffNote = $note !== '' ? $note : null;

        DB::table('tickets')
            ->where('id', $id)
            ->update([
                'assigned_to'  => $forwardTo,
                'handoff_note' => $handoffNote,
                // Do not change updated_at on forward so the "Processing" timeline time
                // (when status became 1) doesn't shift.
            ]);

        if ($handoffNote !== null) {
            $toName = DB::table('users')->where('id', $forwardTo)->value('name');
            DB::table('ticket_replies')->insert([
                'ticket_id'  => $id,
                'user_id'    => auth()->id(),
                'message'    => 'Forwarded to ' . ($toName ?? ('User #' . $forwardTo)) . ': ' . $handoffNote,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('tickets.show', $id)
            ->with('success', 'Ticket forwarded successfully.');
    }

}
