@extends('layouts.app')

@section('title', auth()->user()->role === 'field' ? 'My field desk' : 'Operations overview')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px">{{ now()->format('l, F j') }}</div>
            <h1>{{ auth()->user()->role === 'field' ? 'My field desk' : 'Operations overview' }}</h1>
            <p class="subtitle">
                {{ auth()->user()->role === 'field' ? 'Your assigned work, ready when you are.' : 'Live picture of field work and service activity.' }}
            </p>
        </div>
        @if (in_array(auth()->user()->role, ['admin', 'supervisor'], true))
            <a class="btn btn-primary" href="{{ route('work-orders.create') }}"><span aria-hidden="true">＋</span> New work order</a>
        @endif
    </div>

    <section class="metric-grid" aria-label="Work order summary">
        @if (auth()->user()->role === 'field')
            <div class="metric"><div class="metric-label">New assignments</div><div class="metric-value">{{ $stats['assigned'] }}</div></div>
            <div class="metric"><div class="metric-label">In progress</div><div class="metric-value">{{ $stats['in_progress'] }}</div></div>
            <div class="metric metric-review"><div class="metric-label">Waiting for review</div><div class="metric-value">{{ $stats['for_review'] }}</div></div>
            <div class="metric"><div class="metric-label">Completed</div><div class="metric-value">{{ $stats['completed'] }}</div></div>
        @else
            <div class="metric"><div class="metric-label">Active work orders</div><div class="metric-value">{{ $stats['total'] - $stats['completed'] }}</div></div>
            <div class="metric"><div class="metric-label">Pending / assigned</div><div class="metric-value">{{ $stats['pending'] + $stats['assigned'] }}</div></div>
            <div class="metric metric-review"><div class="metric-label">Awaiting review</div><div class="metric-value">{{ $stats['for_review'] }}</div></div>
            <div class="metric metric-urgent"><div class="metric-label">Urgent active</div><div class="metric-value">{{ $stats['urgent'] }}</div></div>
        @endif
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>{{ auth()->user()->role === 'field' ? 'Your tasks' : 'Recent work orders' }}</h2>
                <div class="table-sub">{{ auth()->user()->role === 'field' ? 'Assigned directly to you' : 'Latest work across field operations' }}</div>
            </div>
            <a class="btn btn-small" href="{{ route('work-orders.index') }}">View all <span aria-hidden="true">→</span></a>
        </div>
        @if ($workOrders->isEmpty())
            <div class="empty"><strong>No work orders yet</strong>New tasks will show here when they are created or assigned.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Work order</th><th>Asset</th><th>Priority</th><th>Status</th><th>Scheduled</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($workOrders as $workOrder)
                            <tr>
                                <td><a class="table-title" href="{{ route('work-orders.show', $workOrder) }}">{{ $workOrder->work_order_number }} · {{ $workOrder->title }}</a><div class="table-sub">{{ str_replace('_', ' ', $workOrder->category) }}</div></td>
                                <td>{{ $workOrder->asset?->asset_number ?? '—' }}</td>
                                <td><span class="badge badge-{{ $workOrder->priority }}">{{ $workOrder->priority }}</span></td>
                                <td><span class="badge badge-{{ $workOrder->status }}">{{ str_replace('_', ' ', $workOrder->status) }}</span></td>
                                <td>{{ $workOrder->scheduled_at?->format('M j, Y') ?? 'Unscheduled' }}</td>
                                <td><a class="btn btn-small" href="{{ route('work-orders.show', $workOrder) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($assetCount !== null)
        <div style="margin-top: 15px; color: var(--muted); font-size: 11px">Asset register: <strong style="color: var(--ink)">{{ number_format($assetCount) }}</strong> records</div>
    @endif
@endsection