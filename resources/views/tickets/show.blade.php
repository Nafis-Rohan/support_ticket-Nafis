@extends('layouts.app')

@section('content')
<div class="container py-4 ticket-details-page">
    <div class="card shadow-sm border-0 ticket-shell">
        <div class="card-header ticket-topbar d-flex justify-content-between align-items-center">
            <a href="{{ url()->previous() }}" class="ticket-top-icon" title="Back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="mb-0 fw-semibold">Ticket Details</h4>
            <div class="d-flex align-items-center gap-3">
                <small class="text-white-50">Ticket ID #{{ $ticket->id }}</small>
                <span class="ticket-top-icon"><i class="bi bi-share"></i></span>
                <span class="ticket-top-icon"><i class="bi bi-printer"></i></span>
            </div>
        </div>

        <div class="card-body">

            {{-- Flash message --}}
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            {{-- Header Info Strip --}}
            @php
            $statusText = $ticket->status == 0 ? 'Pending' : ($ticket->status == 1 ? 'Processing' : 'Solved');
            $statusBadgeClass = $ticket->status == 0 ? 'bg-warning text-dark' : ($ticket->status == 1 ? 'bg-info text-dark' : 'bg-success');
            $isSolved = (int) ($ticket->status ?? 0) === 2;
            @endphp
            <div class="card shadow-sm border-0 mb-3 ticket-meta-card">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <div><i class="bi bi-person-fill text-muted me-2"></i><strong>Reporter:</strong> {{ $ticket->contact_person ?: '—' }}</div>
                            <div class="mt-1"><i class="bi bi-eye-fill text-muted me-2"></i><strong>Subject:</strong> {{ $ticket->subject }}</div>
                        </div>
                        <div class="col-md-4 border-start-md">
                            <div><i class="bi bi-folder-fill text-muted me-2"></i><strong>Category:</strong> {{ $ticket->category_name ?? 'N/A' }}</div>
                            <div class="mt-1"><i class="bi bi-tag-fill text-muted me-2"></i><strong>Sub Category:</strong>
                                @if(!empty($ticket->sub_category_name))
                                    <span class="badge bg-primary">{{ $ticket->sub_category_name }}</span>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 text-md-center border-start-md">
                            <span class="ticket-created-at">{{ optional(\App\Support\DisplayTime::fromUtcStored($ticket->created_at))->format('d M, Y h:i A') }}</span>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <span class="badge rounded-pill px-4 py-2 fs-5 {{ $statusBadgeClass }}">{{ $statusText }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description section is rendered in role/state-specific cards below --}}

            @php
            $handoffNote = trim((string) ($ticket->handoff_note ?? ''));
            $isReceivingAssignee = in_array((int) (auth()->user()->role ?? 0), [1, 2], true)
            && !empty($ticket->assigned_to)
            && (int) $ticket->assigned_to === (int) auth()->id();
            @endphp
            @if($isReceivingAssignee && $handoffNote !== '')
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3" style="background: #fff3cd;">
                    <div class="fw-semibold mb-1">Handoff Message</div>
                    <div class="small" style="font-size: 13px;">{!! nl2br(e($handoffNote)) !!}</div>
                </div>
            </div>
            @endif

            @php
                $assignedName = trim((string) ($ticket->assigned_to_name ?? ''));
                $initials = collect(explode(' ', $assignedName))->filter()->map(function ($w) {
                    return strtoupper(substr($w, 0, 1));
                })->take(2)->implode('');
                $initials = $initials !== '' ? $initials : 'NA';
                $hasAssignedUser = !empty($ticket->assigned_to);
                $role = (int) (auth()->user()->role ?? 0);
                $assignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;
                $canSolveAsAttendee = in_array($role, [1, 2], true)
                    && $assignedToId !== null
                    && $assignedToId === (int) auth()->id();
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h4 class="mb-0 fw-semibold">Problem Description</h4>
                        </div>
                        <div class="card-body">
                            <div class="border rounded p-3 bg-light min-h-140">
                                {!! nl2br(e($ticket->description)) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h4 class="mb-0 fw-semibold">Team &amp; Resolution</h4>
                        </div>
                        <div class="card-body">
                            <div class="small fw-semibold mb-1">Assigned Engineer</div>
                            @if($hasAssignedUser)
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="avatar-circle">{{ $initials }}</span>
                                    <span>{{ $ticket->assigned_to_name }}</span>
                                </div>
                            @else
                                @php
                                    $canAttendFromResolution = in_array((int) (auth()->user()->role ?? 0), [1, 2], true)
                                        && (int) ($ticket->status ?? 0) !== 2
                                        && !empty($isEngineerForCategory);
                                @endphp
                                <div class="d-flex align-items-start gap-2 mb-3">
                                    <div class="alert alert-secondary mb-0 py-2 flex-grow-1">
                                        No one assigned yet.
                                    </div>
                                    @if($canAttendFromResolution)
                                        <form method="POST" action="{{ route('tickets.take_action', $ticket->id) }}" class="m-0">
                                            @csrf
                                            <input type="hidden" name="action" value="attend">
                                            <button type="submit" class="btn btn-primary btn-sm">Attend</button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            <div class="small fw-semibold mb-1">Resolution Details</div>
                            @if((int) ($ticket->status ?? 0) === 2)
                                <div class="alert alert-success mb-0 py-2">
                                    Solved By: {{ $ticket->solved_by_name ?? '—' }}
                                </div>
                            @elseif($hasAssignedUser)
                                <div class="alert alert-info mb-0 py-2">
                                    <strong>{{ $ticket->assigned_to_name }}</strong> Working on this ticket
                                </div>
                            @else
                                <div class="alert alert-secondary mb-0 py-2">
                                    No one working right now.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Branch: show solved message only (when ticket is solved) --}}
            @if((int) (auth()->user()->role ?? 0) === 3 && (int) ($ticket->status ?? 0) === 2)
            @php
            $solvedMsg = trim((string) ($ticket->solved_message ?? ''));
            @endphp
            @if($solvedMsg !== '')
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3" style="background: #d1e7dd;">
                    <div class="fw-semibold mb-1">Solved Message</div>
                    <div class="small">
                        {!! nl2br(e($solvedMsg)) !!}
                    </div>
                </div>
            </div>
            @endif
            @endif

            {{-- Attachment Section --}}
            @php
            $legacyAttachment = !empty($ticket->attachment)
            ? collect([(object) ['file_path' => $ticket->attachment, 'original_name' => null]])
            : collect();
            $allAttachments = ($attachments ?? collect())->merge($legacyAttachment);
            @endphp
            @if($allAttachments->count())
            <div class="mb-4">
                <strong>Attachments:</strong>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach($allAttachments as $file)
                    <a href="{{ asset('storage/' . $file->file_path) }}"
                        class="btn btn-outline-primary btn-sm"
                        target="_blank">
                        <i class="bi bi-paperclip"></i>
                        {{ $file->original_name ?: basename($file->file_path) }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Admin: Priority + Manual Assign (moved below Attachments + Assigned Engineer/Action) --}}
            @if(!$isSolved && auth()->check() && (int) auth()->user()->role === 1)
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-semibold">Priority</h6>
                        </div>
                        <div class="card-body py-3">
                            @if((int) $ticket->status === 2)
                            <p class="mb-0 text-muted small">
                                Current: <strong>{{ $ticket->priority_name ?? 'N/A' }}</strong>.
                                Priority cannot be changed for a solved ticket.
                            </p>
                            @else
                            <form method="POST" action="{{ route('tickets.update_priority', $ticket->id) }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-12 col-md-8">
                                    <label for="priority_id" class="form-label small mb-1">Set priority</label>
                                    <select name="priority_id" id="priority_id" class="form-select form-select-sm">
                                        <option value="">— Not set —</option>
                                        @foreach(($priorities ?? collect()) as $p)
                                        <option value="{{ $p->id }}" {{ (int) ($ticket->priority_id ?? 0) === (int) $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-3">Save</button>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-semibold">Person Assign</h6>
                        </div>
                        <div class="card-body py-3">
                            @if((int) $ticket->status === 2)
                            <p class="mb-0 text-muted small">Assignment cannot be changed for a solved ticket.</p>
                            @elseif(empty($ticket->category_id))
                            <p class="mb-0 text-muted small">Set a category first before assigning an engineer.</p>
                            @elseif(($manualAssignEngineers ?? collect())->isEmpty())
                            <p class="mb-0 text-muted small">No engineer accounts are available to assign.</p>
                            @else
                            <form method="POST" action="{{ route('tickets.assign', $ticket->id) }}" class="d-flex flex-column gap-2">
                                @csrf
                                <div>
                                    <label for="manual_assign_engineer_id" class="form-label small mb-1">Assign to</label>
                                    <select
                                        name="engineer_id"
                                        id="manual_assign_engineer_id"
                                        class="form-select form-select-sm"
                                        required>
                                        <option value="" disabled {{ empty($ticket->assigned_to) ? 'selected' : '' }}>— Select user —</option>
                                        @foreach($manualAssignEngineers as $eng)
                                        @php
                                        $displayRole = (int) ($eng->role ?? 0) === 1 ? 'Support' : (((int) ($eng->role ?? 0) === 2) ? 'Developer' : 'User');
                                        $displayName = trim((string) ($eng->name ?? ''));
                                        if ($displayName === '') {
                                        $displayName = 'User #' . (int) ($eng->id ?? 0);
                                        }
                                        @endphp
                                        <option value="{{ $eng->id }}"
                                            {{ (int) ($ticket->assigned_to ?? 0) === (int) $eng->id ? 'selected' : '' }}>
                                            {{ $displayName }} ({{ $displayRole }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="manual_assign_note" class="form-label small mb-1">
                                        Message <span class="text-muted fw-normal">(optional)</span>
                                    </label>
                                    <textarea
                                        name="note"
                                        id="manual_assign_note"
                                        rows="2"
                                        class="form-control form-control-sm"
                                        placeholder="Add instructions/message for the engineer..."></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success btn-sm px-3">Assign</button>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @elseif(!$isSolved && auth()->check() && (int) auth()->user()->role === 2)
            {{-- Developer: read-only priority + Forward (developers only) --}}
            @php
            $devAssignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;
            $devCanForward = (int) ($ticket->status ?? 0) !== 2
            && $devAssignedToId !== null
            && $devAssignedToId === (int) auth()->id();
            $otherDevelopers = ($forwardDeveloperOptions ?? collect())->filter(function ($u) {
            return (int) $u->id !== (int) auth()->id();
            });
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-semibold">Priority</h6>
                        </div>
                        <div class="card-body py-3">
                            @if(!empty($ticket->priority_name))
                            @if($ticket->priority_name == 'High')
                            <span class="badge bg-success fs-6 px-3 py-2">High</span>
                            @elseif($ticket->priority_name == 'Medium')
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Medium</span>
                            @elseif($ticket->priority_name == 'Low')
                            <span class="badge bg-info fs-6 px-3 py-2">Low</span>
                            @elseif($ticket->priority_name == 'Urgent')
                            <span class="badge bg-danger fs-6 px-3 py-2">Urgent</span>
                            @else
                            <span class="badge bg-secondary fs-6 px-3 py-2">{{ $ticket->priority_name }}</span>
                            @endif
                            @else
                            <span class="badge bg-secondary fs-5 px-4 py-2">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-semibold">Forward</h6>
                        </div>
                        <div class="card-body py-3">
                            @if((int) $ticket->status === 2)
                            <p class="mb-0 text-muted small">Forward is not available for a solved ticket.</p>
                            @elseif(!$devCanForward)
                            <p class="mb-0 text-muted small">Forward is available when you are attending this ticket.</p>
                            @elseif($otherDevelopers->isEmpty())
                            <p class="mb-0 text-muted small">No other developers to forward to.</p>
                            @else
                            <form method="POST" action="{{ route('tickets.take_action', $ticket->id) }}" class="d-flex flex-column gap-2">
                                @csrf
                                <input type="hidden" name="action" value="forward">
                                <div>
                                    <label for="dev_forward_to" class="form-label small mb-1">Forward to</label>
                                    <select name="forward_to" id="dev_forward_to" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>— Select developer —</option>
                                        @foreach($otherDevelopers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} (Developer)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="dev_forward_note" class="form-label small mb-1">
                                        Message <span class="text-muted fw-normal">(optional)</span>
                                    </label>
                                    <textarea name="note" id="dev_forward_note" rows="2" class="form-control form-control-sm" placeholder="Optional note for the next developer..."></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success btn-sm px-3">Forward</button>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Assigned Engineer + Action cards (side-by-side) --}}
            @if(auth()->user()->role != 3 && !$isSolved && !in_array((int) ($ticket->status ?? 0), [0, 1], true))
            @php
            $role = (int) (auth()->user()->role ?? 0);
            $isEngineer = $role === 2;
            $isMappedForCategory = (bool) ($isEngineerForCategory ?? false);
            $assignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;
            $isSelfAssigned = $assignedToId !== null && $assignedToId === (int) auth()->id();
            // Admin (1) or Engineer (2) who is the assigned attendee can use the Solve card
            $canSolveAsAttendee = in_array($role, [1, 2], true)
            && $assignedToId !== null
            && $assignedToId === (int) auth()->id();
            @endphp
            <div class="row g-3 mb-3 mt-1">
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                            <i class="bi bi-person-check"></i>
                            <h4 class="mb-0 fw-semibold">Assigned Engineer</h4>
                        </div>
                        <div class="card-body">
                            <div class="ticket-pill ticket-pill-contact">
                                @if(!empty($ticket->assigned_to))
                                {{ $ticket->assigned_to_name ?? 'Unknown' }}
                                @else
                                Unassigned
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h4 class="mb-0">Action</h4>
                        </div>
                        <div class="card-body py-4">
                            @php
                            $role = (int) (auth()->user()->role ?? 0);
                            $isEngineer = $role === 2;
                            $isAdmin = $role === 1;
                            $isMappedForCategory = (bool) ($isEngineerForCategory ?? false);
                            $assignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;
                            $isSelfAssigned = $assignedToId !== null && $assignedToId === (int) auth()->id();
                            @endphp

                            @if((int) $ticket->status === 2)
                            <div class="alert alert-success mb-0">
                                <strong>Solved By:</strong> {{ $ticket->solved_by_name ?? '—' }}
                            </div>
                            @elseif($isEngineer && ($isMappedForCategory || $isSelfAssigned))
                            @if($assignedToId !== null && !$isSelfAssigned)
                            <div class="alert alert-info mb-0">
                                <strong>{{ $ticket->assigned_to_name ?? 'An engineer' }}</strong> is working on this ticket.
                            </div>
                            @elseif($isSelfAssigned)
                            <div class="alert alert-success mb-0">
                                You are working on this ticket.
                            </div>
                            @if($isEngineer)
                            <p class="text-muted small mb-0 mt-2">
                                Use the <strong>Forward</strong> section above to send this ticket to another developer.
                            </p>
                            @endif
                            @else
                            {{-- Not assigned yet: allow Attend for mapped engineer --}}
                            <form method="POST" action="{{ route('tickets.take_action', $ticket->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="attend">
                                <button type="submit" class="btn btn-primary btn-sm">Attend</button>
                            </form>
                            @endif
                            @elseif($isEngineer && !$isMappedForCategory)
                            <div class="alert alert-danger mb-0">
                                You are not mapped for this ticket&rsquo;s category.
                            </div>
                            @else
                            {{-- Admin / non-engineer view --}}
                            @if(!empty($ticket->assigned_to))
                            @if($isSelfAssigned && $isAdmin)
                            <div class="alert alert-success mb-0">
                                You are working on this ticket.
                            </div>
                            @else
                            <div class="alert alert-info mb-0">
                                <strong>{{ $ticket->assigned_to_name ?? 'Someone' }}</strong> is working on this ticket.
                            </div>
                            @endif
                            @else
                            <div class="alert alert-secondary mb-2">
                                No one is attending this ticket yet.
                            </div>
                            @if($isAdmin)
                            <form method="POST" action="{{ route('tickets.take_action', $ticket->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="attend">
                                <button type="submit" class="btn btn-primary btn-sm">Attend</button>
                            </form>
                            @endif
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Solve card below both Assigned Engineer + Action cards --}}
            @if(auth()->user()->role != 3 && (int) $ticket->status !== 2 && ($canSolveAsAttendee ?? false))
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">Solve</h6>
                </div>
                <div class="card-body py-3">
                    <form method="POST" action="{{ route('tickets.update', $ticket->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="2">

                        <div class="mb-2">
                            <label for="solved_message" class="form-label small mb-1">
                                Message (optional)
                            </label>
                            <textarea name="solved_message" id="solved_message" rows="2" class="form-control form-control-sm" placeholder="Add an optional note for the solved ticket..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">Solve</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Ticket timeline intentionally removed as requested --}}
        </div>
    </div>
</div>

<style>
    .ticket-shell {
        border-radius: 12px;
        overflow: hidden;
        background: #f1f3f5;
    }

    .ticket-topbar {
        background: linear-gradient(90deg, #4a9bd6, #6aa9dd);
        color: #fff;
    }

    .ticket-top-icon {
        color: #e8f3fb;
        text-decoration: none;
        font-size: 1.2rem;
        line-height: 1;
    }

    .ticket-meta-card {
        border-radius: 12px;
    }

    .ticket-created-at {
        font-size: 1.15rem;
        font-weight: 500;
    }

    .avatar-circle {
        width: 46px;
        height: 46px;
        border-radius: 999px;
        background: #e7ecf3;
        color: #4e5b6b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.35rem;
    }

    .min-h-140 {
        min-height: 140px;
    }

    .ticket-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 600;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background: #f3f6f9;
        color: #495057;
    }

    .ticket-pill-contact {
        background: #e9edf2;
        color: #495057;
    }

    .timeline-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .timeline-horizontal {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: space-between;
        position: relative;
        align-items: center;
    }

    .timeline-horizontal::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 26px;
        height: 3px;
        background: #e9ecef;
        z-index: 1;
    }

    .timeline-horizontal::after {
        content: "";
        position: absolute;
        left: 0;
        top: 26px;
        height: 4px;
        width: var(--progress, 0%);
        background: var(--progress-color, #28a745);
        z-index: 2;
        border-radius: 3px;
    }

    .timeline-step {
        position: relative;
        text-align: center;
        flex: 1 1 0;
        min-width: 140px;
    }

    .timeline-step .dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #dee2e6;
        box-shadow: 0 0 0 3px #e9ecef;
        margin: 0 auto;
        position: relative;
        z-index: 3;
    }

    .timeline-step .timeline-icon {
        font-size: 1.8rem;
        color: #198754;
        line-height: 1;
        margin-bottom: 6px;
    }

    .timeline-step.active .dot {
        background: #28a745;
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
    }

    .timeline-step.done .dot {
        background: #28a745;
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
    }

    .timeline-step .label {
        margin-top: 11px;
        font-weight: 600;
        font-size: .95rem;
    }

    .timeline-step.active .label {
        color: #28a745;
    }

    .timeline-step.done .label {
        color: #28a745;
    }

    .timeline-step .time {
        margin-top: 2px;
        font-size: .8rem;
        color: #6c757d;
    }
</style>


@endsection