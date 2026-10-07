@extends('layouts.app')

@section('title', auth()->user()->role === 'field' ? 'My tasks' : 'Work orders')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px">Field operations</div>
            <h1>{{ auth()->user()->role === 'field' ? 'My tasks' : 'Work orders' }}</h1>
            <p class="subtitle">{{ auth()->user()->role === 'field' ? 'Only tasks assigned to your account.' : 'Create, assign, and monitor field work.' }}</p>
        </div>
        @if (in_array(auth()->user()->role, ['admin', 'supervisor'], true))
            <a class="btn btn-primary" href="{{ route('work-orders.create') }}"><span aria-hidden="true">＋</span> New work order</a>
        @endif
    </div>

    <section class="panel">
        <form class="filters" method="GET" action="{{ route('work-orders.index') }}">
            <div class="field" style="min-width: 220px; flex: 1">
                <label for="q">Find work</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Work order number or title">
            </div>
            <div class="field" style="min-width: 145px">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'assigned', 'in_progress', 'for_review', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width: 125px">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="">All priorities</option>
                    @foreach (['low', 'normal', 'high', 'urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn" type="submit">Filter</button>
            @if ($filters)
                <a class="btn" href="{{ route('work-orders.index') }}">Clear</a>
            @endif
        </form>
        @if ($workOrders->isEmpty())
            <div class="empty"><strong>No matching work orders</strong>Try a different filter or search term.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Work order</th><th>Category</th><th>Asset</th><th>Assigned crew</th><th>Priority</th><th>Status</th><th>Schedule</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($workOrders as $workOrder)
                            <tr>
                                <td><a class="table-title" href="{{ route('work-orders.show', $workOrder) }}">{{ $workOrder->work_order_number }}</a><div class="table-sub">{{ $workOrder->title }}</div></td>
                                <td>{{ ucfirst($workOrder->category) }}</td>
                                <td>{{ $workOrder->asset?->asset_number ?? '—' }}</td>
                                <td>{{ $workOrder->assignedPersonnel->pluck('name')->join(', ') ?: 'Unassigned' }}</td>
                                <td><span class="badge badge-{{ $workOrder->priority }}">{{ $workOrder->priority }}</span></td>
                                <td><span class="badge badge-{{ $workOrder->status }}">{{ str_replace('_', ' ', $workOrder->status) }}</span></td>
                                <td>{{ $workOrder->scheduled_at?->format('M j, Y') ?? '—' }}</td>
                                <td><a class="btn btn-small" href="{{ route('work-orders.show', $workOrder) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination"><span>{{ $workOrders->firstItem() }}–{{ $workOrders->lastItem() }} of {{ $workOrders->total() }}</span>{{ $workOrders->links() }}</div>
        @endif
    </section>
@endsection