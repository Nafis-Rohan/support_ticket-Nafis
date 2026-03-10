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

            {{-- Header Section --}}
            <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <div>
                    <p class="mb-1"><strong>Subject:</strong> {{ $ticket->subject }}</p>
                </div>
                <p class="mb-1"><strong>Category:</strong> {{ $ticket->category_name ?? 'N/A' }}</p>
                @if(!empty($ticket->sub_category_name))
                    <p class="mb-1"><strong>Sub Category:</strong> {{ $ticket->sub_category_name }}</p>
                @endif
                <div class="text-end">
                    <p class="mb-1"><strong>Created at:</strong> {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M, Y h:i A') }}</p>
                    @if(!empty($ticket->contact_person))
                        <p class="mb-0"><strong>Contact Person:</strong> {{ $ticket->contact_person }}</p>
                    @endif
                </div>
            </div>

            {{-- Description Section --}}
            <div class="mb-4">
                <p class="mb-1"><strong>Description:</strong></p>
                <div class="border rounded p-3 bg-light">
                    {!! nl2br(e($ticket->description)) !!}
                </div>
            </div>

            {{-- Attachment Section --}}
            @if($ticket->attachment)
                <div class="mb-4">
                    <strong>Attachment:</strong>
                    <a href="{{ asset('storage/' . $ticket->attachment) }}" class="btn btn-outline-primary btn-sm ms-2" target="_blank">
                        <i class="bi bi-paperclip"></i> View / Download
                    </a>
                </div>
            @endif
            {{-- show only current assigned engineer --}}
            <p class="mb-0">
                <strong>Assigned Engineer:</strong>
                @if(!empty($ticket->assigned_to))
                    {{ $ticket->assigned_to_name ?? 'Unknown' }}
                @else
                    <span class="text-muted">Unassigned</span>
                @endif
            </p>

            {{-- Forward to next engineer (admin or current engineer only) --}}
            @if($nextEngineer && (auth()->user()->role === 1 || (auth()->user()->role === 2 && auth()->id() === $ticket->assigned_to)))
                <form method="POST" action="{{ route('tickets.forward', $ticket->id) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        Forward to Next Engineer ({{ $nextEngineer->name }})
                    </button>
                </form>
            @endif

            {{-- Status Section --}}
            <div class="mb-4">
                <p class="mb-1"><strong>Status:</strong>
                    @if ($ticket->status == 0)
                        <span class="badge bg-secondary">Pending</span>
                    @elseif ($ticket->status == 1)
                        <span class="badge bg-warning text-dark">Processing</span>
                    @else
                        <span class="badge bg-success">Solved</span>
                    @endif
                </p>
                @if(!empty($ticket->solved_by))
                    <p class="mb-0"><strong>Solved By:</strong> {{ $ticket->solved_by_name ?? 'N/A' }}</p>
                @endif
            </div>
{{-- Horizontal Status Timeline --}}
    <div class="timeline-wrap my-4">
        <ul class="timeline-horizontal">
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
                        @if($step['time'])
                            {{ $step['time']->format('d M, Y h:i A') }}
                        @else
                            —
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

            {{-- Replies Section --}}
            <hr>
            <h5 class="mb-3">💬 Replies</h5>
            
            @if($replies->isEmpty())
                <p class="text-muted">No replies yet.</p>
                @else
                <div class="mb-4">
                    @foreach($replies as $reply)
                        <div class="border rounded p-3 mb-2 bg-light">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $reply->user_name }}</strong>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($reply->created_at)->format('d M, Y h:i A') }}
                                </small>
                            </div>
                            <div class="mt-2">
                                {!! nl2br(e($reply->message)) !!}
                            </div>
                {{-- If this row has a NOTE --}}
                    @if($reply->note && auth()->user()->role != 3)
                        <div class="mt-2 p-2 bg-warning bg-opacity-25 border-start border-3 border-warning">
                            <strong>📝 Admin Note:</strong><br>
                            {!! nl2br(e($reply->note)) !!}
                        </div>
                    @endif

                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Reply Form: only for Admin (1) --}}
            @if(in_array(auth()->user()->role, [1]))
                <div class="mt-4">
                    <h5 class="mb-3">➕ Add a Reply</h5>
                    <form method="POST" action="{{ route('tickets.replies.store', $ticket->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" id="message" rows="4" class="form-control" required>{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            @endif
            
        <hr>

        <!-- Assign Enginner section -->
        @if(auth()->user()->role == 1)      
        <div class="mt-3 mb-3">
            <h5 class="mb-3">👨‍💻 Assign to Engineer</h5>

            @if(session('error'))
                <div class="alert alert-danger py-1 px-2 mb-2">
                    {{ session('error') }}
                </div>
            @endif

        <form method="POST" action="{{ route('tickets.assign', $ticket->id) }}" class="row g-2 align-items-end">
            @csrf

            {{-- Engineer dropdown --}}
            <div class="col-md-3">
                <label class="form-label mb-1">Engineer</label>
                <select name="engineer_id" class="form-control form-control-sm">
                    <option value="">-- Select Engineer --</option>
                    @foreach($engineers as $engineer)
                        <option value="{{ $engineer->id }}"
                            {{ $ticket->assigned_to == $engineer->id ? 'selected' : '' }}>
                            {{ $engineer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Note textarea (optional) --}}            
            <div class="col-md-6">
                <label for="note" class="form-label fw-semibold">Note to Engineer (optional)</label>
                <textarea name="note" id="note" rows="1"
                class="form-control form-control-sm"
                placeholder="Write instructions or comments for the engineer...">{{ old('note') }}</textarea>
            </div>

                {{-- Assign button --}}
                <div class="col-md-3 text-end">
                        <button type="submit" class="btn btn-sm btn-info px-3 mb-2">
                            Assign
                        </button>
                </div>
            </form>

        @if($ticket->assigned_to)
            <p class="mt-2 mb-0">
                <strong>Currently assigned to:</strong> {{ $ticket->assigned_to_name ?? 'Unknown' }}
            </p>
            @endif
            </div>
        @endif


            {{-- Admin Section: status update --}}
            @if (in_array(auth()->user()->role, [1,2]))
                <hr>
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <h5 class="mb-3 mb-md-0">🛠 Update Ticket Status</h5>
                    <form method="POST" action="{{ route('tickets.update', $ticket->id) }}" class="d-flex align-items-center bg-light p-3 rounded shadow-sm flex-wrap">
                        @csrf
                        @method('PUT')

                        <div class="me-3 mb-2 mb-md-0">
                            <label for="status" class="form-label fw-semibold mb-0 me-2">Status:</label>
                            <select name="status" id="status" class="form-select form-select-sm d-inline-block w-auto shadow-sm">
                                <option value="0" {{ $ticket->status == 0 ? 'selected' : '' }}>Pending</option>
                                <option value="1" {{ $ticket->status == 1 ? 'selected' : '' }}>Processing</option>
                                <option value="2" {{ $ticket->status == 2 ? 'selected' : '' }}>Solved</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-info btn-sm ms-2">
                                <i class="bi bi-check-circle"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
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
    background: #adb5bd;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #e9ecef;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}
.timeline-step.active .dot {
    background: #008000	;
    box-shadow: 0 0 0 3px rgba(36, 255, 65, 0.25);
}
.timeline-step.done .dot {
    background: #5050ffff;
    box-shadow: 0 0 0 3px rgba(33, 88, 164, 0.25);
}
.timeline-step .label {
    margin-top: 11px;
    font-weight: 600;
    font-size: .95rem;
}
.timeline-step .time {
    margin-top: 2px;
    font-size: .8rem;
    color: #6c757d;
}
</style>


@endsection
