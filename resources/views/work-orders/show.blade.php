@extends('layouts.app')

@section('title', $workOrder->work_order_number)

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px"><a href="{{ route('work-orders.index') }}">Work orders</a> / {{ $workOrder->work_order_number }}</div>
            <h1>{{ $workOrder->title }}</h1>
            <p class="subtitle">{{ $workOrder->work_order_number }} · {{ ucfirst($workOrder->category) }}</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center">
            <span class="badge badge-{{ $workOrder->priority }}">{{ $workOrder->priority }}</span>
            <span class="badge badge-{{ $workOrder->status }}">{{ str_replace('_', ' ', $workOrder->status) }}</span>
        </div>
    </div>

    <div class="split">
        <div class="stack">
            <section class="panel">
                <div class="panel-head"><h2>Work details</h2></div>
                <div class="detail-body">
                    <div class="detail-grid">
                        <div><div class="detail-label">Instructions</div><div class="detail-value">{{ $workOrder->description }}</div></div>
                        <div><div class="detail-label">Assigned personnel</div><div class="detail-value">{{ $workOrder->assignedPersonnel->pluck('name')->join(', ') ?: 'Not assigned' }}</div></div>
                        <div><div class="detail-label">Related asset</div><div class="detail-value">
                            @if ($workOrder->asset)
                                <a href="{{ route('assets.show', $workOrder->asset) }}">{{ $workOrder->asset->asset_number }} · {{ $workOrder->asset->name }}</a>
                            @else
                                Not linked
                            @endif
                        </div></div>
                        <div><div class="detail-label">Scheduled</div><div class="detail-value">{{ $workOrder->scheduled_at?->format('M j, Y') ?? 'Unscheduled' }}</div></div>
                        <div><div class="detail-label">Created by</div><div class="detail-value">{{ $workOrder->creator->name }}</div></div>
                        <div><div class="detail-label">Date assigned</div><div class="detail-value">{{ $workOrder->date_assigned?->format('M j, Y g:i A') ?? '—' }}</div></div>
                    </div>
                    @if ($workOrder->work_performed)
                        <div style="border-top: 1px solid var(--line); margin-top: 18px; padding-top: 17px">
                            <div class="detail-label">Work performed</div>
                            <div class="detail-value">{{ $workOrder->work_performed }}</div>
                        </div>
                    @endif
                    @if ($workOrder->field_notes)
                        <div style="margin-top: 14px"><div class="detail-label">Field notes</div><div class="detail-value">{{ $workOrder->field_notes }}</div></div>
                    @endif
                    @if ($workOrder->completion_photo_path)
                        <div style="margin-top: 14px"><a class="btn btn-small" target="_blank" rel="noreferrer" href="{{ \Illuminate\Support\Facades\Storage::url($workOrder->completion_photo_path) }}">View completion photo</a></div>
                    @endif
                    @if ($workOrder->completed_at)
                        <div style="margin-top: 14px"><div class="detail-label">Approved and closed</div><div class="detail-value">{{ $workOrder->completed_at->format('M j, Y g:i A') }}{{ $workOrder->supervisor ? ' · '.$workOrder->supervisor->name : '' }}</div></div>
                    @endif
                </div>
            </section>

            @if (in_array(auth()->user()->role, ['admin', 'supervisor'], true) && in_array($workOrder->status, ['pending', 'assigned'], true))
                <section class="panel">
                    <div class="panel-head"><h2>Assign field crew</h2></div>
                    <form class="form-panel" method="POST" action="{{ route('work-orders.assign', $workOrder) }}">
                        @csrf
                        @method('PUT')
                        <div class="field">
                            <label for="assigned_personnel">Field personnel</label>
                            <select id="assigned_personnel" name="assigned_personnel[]" multiple required size="4">
                                @foreach ($fieldPersonnel as $person)
                                    <option value="{{ $person->id }}" @selected($workOrder->assignedPersonnel->contains('id', $person->id))>{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit">Save assignment</button></div>
                    </form>
                </section>
            @endif

            @if (auth()->user()->role === 'field' && $workOrder->status === 'assigned')
                <section class="panel form-panel">
                    <h2 style="margin-bottom: 14px">Ready to begin?</h2>
                    <form method="POST" action="{{ route('work-orders.start', $workOrder) }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">Start work</button>
                    </form>
                </section>
            @endif

            @if (auth()->user()->role === 'field' && $workOrder->status === 'in_progress')
                <section class="panel">
                    <div class="panel-head"><h2>Record field update</h2></div>
                    <form class="form-panel" method="POST" action="{{ route('work-orders.updates.store', $workOrder) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div class="field field-wide"><label for="update_work_performed">Work performed so far</label><textarea id="update_work_performed" name="work_performed">{{ old('work_performed') }}</textarea></div>
                            <div class="field field-wide"><label for="update_notes">Field notes</label><textarea id="update_notes" name="notes">{{ old('notes') }}</textarea></div>
                            <div class="field field-wide"><label for="update_photo">Photo documentation</label><input id="update_photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"></div>
                        </div>
                        <div class="form-actions"><button class="btn" type="submit">Save update</button></div>
                    </form>
                </section>
                <section class="panel">
                    <div class="panel-head"><h2>Submit for supervisor review</h2></div>
                    <form class="form-panel" method="POST" action="{{ route('work-orders.submit', $workOrder) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div class="field field-wide"><label for="work_performed">Work performed</label><textarea id="work_performed" name="work_performed" required>{{ old('work_performed', $workOrder->work_performed) }}</textarea></div>
                            <div class="field field-wide"><label for="field_notes">Completion notes</label><textarea id="field_notes" name="field_notes">{{ old('field_notes', $workOrder->field_notes) }}</textarea></div>
                            <div class="field field-wide"><label for="photo">Completion photo</label><input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"></div>
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit">Submit completion</button></div>
                    </form>
                </section>
            @endif

            @if (in_array(auth()->user()->role, ['admin', 'supervisor'], true) && $workOrder->status === 'for_review')
                <section class="panel form-panel">
                    <h2 style="margin-bottom: 14px">Supervisor review</h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: start">
                        <form method="POST" action="{{ route('work-orders.approve', $workOrder) }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Approve and close</button>
                        </form>
                        <form method="POST" action="{{ route('work-orders.return', $workOrder) }}" style="display: flex; flex: 1; gap: 8px; min-width: 260px">
                            @csrf
                            <input name="correction_notes" aria-label="Correction instructions" placeholder="Explain the correction needed" required maxlength="3000" style="flex: 1; min-width: 0; border: 1px solid var(--line); border-radius: 5px; padding: 8px 10px; font-size: 12px">
                            <button class="btn btn-danger" type="submit">Return</button>
                        </form>
                    </div>
                </section>
            @endif
        </div>

        <aside class="panel">
            <div class="panel-head"><h2>Activity</h2></div>
            @if ($workOrder->updates->isEmpty())
                <div class="empty">No field activity recorded.</div>
            @else
                <div class="timeline">
                    @foreach ($workOrder->updates as $update)
                        <div class="timeline-item">
                            <div class="timeline-title">{{ str_replace('_', ' ', $update->update_type) }} · {{ $update->user->name }}</div>
                            @if ($update->work_performed)<div class="timeline-note">{{ $update->work_performed }}</div>@endif
                            @if ($update->notes)<div class="timeline-note">{{ $update->notes }}</div>@endif
                            @if ($update->photo_path)<a class="timeline-note" target="_blank" rel="noreferrer" href="{{ \Illuminate\Support\Facades\Storage::url($update->photo_path) }}">View photo</a>@endif
                            <div class="timeline-time">{{ $update->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
@endsection