@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">🎫 Ticket Details</h4>
            <small class="text-light">Ticket ID: #{{ $ticket->id }}</small>
        </div>

        <div class="card-body">

            {{-- Flash message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header Info Strip (Contact, Subject, Category, Sub Category) --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 small">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span><strong>Contact Person:</strong> {{ $ticket->contact_person ?: '—' }}</span>
                    <span class="text-muted">|</span>
                    <span><strong>Subject:</strong> {{ $ticket->subject }}</span>
                    <span class="text-muted">|</span>
                    <span><strong>Category:</strong> {{ $ticket->category_name ?? 'N/A' }}</span>
                    <span class="text-muted">|</span>
                    <span><strong>Sub Category:</strong>
                        @if(!empty($ticket->sub_category_name))
                            <span class="badge bg-primary">{{ $ticket->sub_category_name }}</span>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="mt-2 mt-md-0">
                    <span>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M, Y h:i A') }}</span>
                </div>
            </div>

            {{-- Status pill (matches screenshot UI) --}}
            @php
                $statusText = $ticket->status == 0 ? 'Pending' : ($ticket->status == 1 ? 'Processing' : 'Solved');
                $statusBadgeClass = $ticket->status == 0 ? 'bg-secondary' : ($ticket->status == 1 ? 'bg-primary' : 'bg-success');
            @endphp
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge {{ $statusBadgeClass }} rounded-pill px-4 py-2 fs-6">{{ $statusText }}</span>
                    </div>
                </div>
            </div>

            {{-- Description Section --}}
            <div class="mb-4">
                <p class="mb-1"><strong>Description:</strong></p>
                <div class="border rounded p-3 bg-light">
                    {!! nl2br(e($ticket->description)) !!}
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
            @if(auth()->check() && (int) auth()->user()->role === 1)
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
                                <h6 class="mb-0 fw-semibold">Manual Assign</h6>
                            </div>
                            <div class="card-body py-3">
                                @if((int) $ticket->status === 2)
                                    <p class="mb-0 text-muted small">Assignment cannot be changed for a solved ticket.</p>
                                @elseif(empty($ticket->category_id))
                                    <p class="mb-0 text-muted small">Set a category first before assigning an engineer.</p>
                                @elseif(($manualAssignEngineers ?? collect())->isEmpty())
                                    <p class="mb-0 text-muted small">No engineers are mapped to this ticket&rsquo;s category.</p>
                                @else
                                    <form method="POST" action="{{ route('tickets.assign', $ticket->id) }}" class="d-flex flex-column gap-2">
                                        @csrf
                                        <div>
                                            <label for="manual_assign_engineer_id" class="form-label small mb-1">Engineer</label>
                                            <select
                                                name="engineer_id"
                                                id="manual_assign_engineer_id"
                                                class="form-select form-select-sm"
                                                required>
                                                <option value="" disabled {{ empty($ticket->assigned_to) ? 'selected' : '' }}>— Select engineer —</option>
                                                @foreach($manualAssignEngineers as $eng)
                                                    <option value="{{ $eng->id }}"
                                                        {{ (int) ($ticket->assigned_to ?? 0) === (int) $eng->id ? 'selected' : '' }}>
                                                        {{ $eng->name }}
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
            @endif

            {{-- Handoff message before Assigned Engineer / Action --}}
            @php
                $handoffNote = trim((string) ($ticket->handoff_note ?? ''));
                $isReceivingEngineer = (int) auth()->user()->role === 2
                    && !empty($ticket->assigned_to)
                    && (int) $ticket->assigned_to === (int) auth()->id();
            @endphp
            @if($isReceivingEngineer && $handoffNote !== '')
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-3" style="background: #fff3cd;">
                        <div class="fw-semibold mb-1">Handoff Message</div>
                        <div class="small" style="font-size: 13px;">{!! nl2br(e($handoffNote)) !!}</div>
                    </div>
                </div>
            @endif

            {{-- Assigned Engineer + Action cards (side-by-side) --}}
            @if(auth()->user()->role != 3)
                @php
                    $role = (int) (auth()->user()->role ?? 0);
                    $isEngineer = $role === 2;
                    $isMappedForCategory = (bool) ($isEngineerForCategory ?? false);
                    $assignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;
                    $isSelfAssigned = $isEngineer && $assignedToId !== null && $assignedToId === (int) auth()->id();
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
                                    $role = auth()->user()->role ?? 0;
                                    $isEngineer = (int) $role === 2;
                                    $isMappedForCategory = (bool) ($isEngineerForCategory ?? false);
                                    $assignedToId = !empty($ticket->assigned_to) ? (int) $ticket->assigned_to : null;
                                    $isSelfAssigned = $isEngineer && $assignedToId !== null && $assignedToId === (int) auth()->id();
                                @endphp

                                @if((int) $ticket->status === 2)
                                    <div class="alert alert-success mb-0">
                                        <strong>Solved By:</strong> {{ $ticket->solved_by_name ?? '—' }}
                                    </div>
                                @elseif($isEngineer && $isMappedForCategory)
                                    @if($assignedToId !== null && !$isSelfAssigned)
                                        <div class="alert alert-info mb-0">
                                            <strong>{{ $ticket->assigned_to_name ?? 'An engineer' }}</strong> is working on this ticket.
                                        </div>
                                    @elseif($isSelfAssigned)
                                        
                                        @if(!($manualAssignEngineers ?? collect())->isEmpty())
                                            <form method="POST" action="{{ route('tickets.take_action', $ticket->id) }}" class="mb-0">
                                                @csrf
                                                <input type="hidden" name="action" value="forward">
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Forward to</label>
                                                    <select name="forward_to" class="form-select form-select-sm" required>
                                                        <option value="" disabled selected>— Select engineer —</option>
                                                        @foreach($manualAssignEngineers as $eng)
                                                            @if((int) $eng->id === (int) auth()->id())
                                                                @continue
                                                            @endif
                                                            <option value="{{ $eng->id }}">{{ $eng->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Note (optional)</label>
                                                    <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Optional note for the next engineer..."></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm">Forward</button>
                                            </form>
                                        @else
                                            <p class="text-muted small mb-0">No mapped engineers to forward for this category.</p>
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
                                        <div class="alert alert-info mb-0">
                                            <strong>{{ $ticket->assigned_to_name ?? 'An engineer' }}</strong> is working on this ticket.
                                        </div>
                                    @else
                                        <div class="alert alert-secondary mb-0">
                                            No one is attending this ticket yet.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Solve card below both Assigned Engineer + Action cards --}}
            @if(auth()->user()->role != 3 && (int) $ticket->status !== 2 && (isset($isSelfAssigned) ? $isSelfAssigned : false))
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

            {{-- Horizontal Status Timeline --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <div class="timeline-wrap">
                        <ul class="timeline-horizontal"
                            style="--progress: {{ ((int) $ticket->status) === 0 ? '0%' : (((int) $ticket->status) === 1 ? '50%' : '100%') }}; --progress-color: {{ ((int) $ticket->status) === 0 ? '#e9ecef' : '#28a745' }};">
                            @php
                                $current = (int) $ticket->status; // 0=pending,1=processing,2=solved
                                $steps = [
                                    ['label' => 'Pending',    'time' => $pendingAt],
                                    ['label' => 'Processing', 'time' => $processingAt],
                                    ['label' => 'Solved',     'time' => $solvedAt],
                                ];
                            @endphp

                            @foreach($steps as $i => $step)
                                @php
                                    $isDone   = $current > $i;
                                    $isActive = $current == $i;
                                @endphp

                                <li class="timeline-step {{ $isDone ? 'done' : '' }} {{ $isActive ? 'active' : '' }}">
                                    <div class="dot"></div>
                                    <div class="label">{{ $step['label'] }}</div>
                                    <div class="time">
                                        @if($step['time'] && ($isDone || $isActive))
                                            {{ $step['time']->format('d M, Y h:i A') }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Replies + admin assignment/status controls intentionally removed to keep UI clean --}}
        </div>
    </div>
</div>

<style>
.ticket-pill{
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

.ticket-pill-contact{
    background: #e9edf2;
    color: #495057;
}

.timeline-wrap { width: 100%; overflow-x: auto; }
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
