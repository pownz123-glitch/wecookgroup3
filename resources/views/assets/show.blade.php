@extends('layouts.app')

@section('title', $asset->asset_number)

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px"><a href="{{ route('assets.index') }}">Assets</a> / {{ $asset->asset_number }}</div>
            <h1>{{ $asset->name }}</h1>
            <p class="subtitle">{{ $asset->asset_number }} · {{ $asset->asset_type }}</p>
        </div>
        <div style="display: flex; align-items: center; gap: 9px">
            <span class="badge">{{ str_replace('_', ' ', $asset->status) }}</span>
            @if (in_array(auth()->user()->role, ['admin', 'operations'], true))
                <a class="btn btn-small" href="{{ route('assets.edit', $asset) }}">Edit asset</a>
            @endif
        </div>
    </div>

    <div class="split">
        <section class="panel">
            <div class="panel-head"><h2>Asset details</h2></div>
            <div class="detail-body">
                <div class="detail-grid">
                    <div><div class="detail-label">Serial number</div><div class="detail-value">{{ $asset->serial_number ?: 'Not recorded' }}</div></div>
                    <div><div class="detail-label">Condition</div><div class="detail-value">{{ ucfirst($asset->condition) }}</div></div>
                    <div><div class="detail-label">Installation / acquisition</div><div class="detail-value">{{ $asset->installation_date?->format('M j, Y') ?? 'Not recorded' }}</div></div>
                    <div><div class="detail-label">Registered</div><div class="detail-value">{{ $asset->created_at->format('M j, Y') }}</div></div>
                </div>
                @if ($asset->description)
                    <div style="border-top: 1px solid var(--line); margin-top: 18px; padding-top: 17px"><div class="detail-label">Additional details</div><div class="detail-value">{{ $asset->description }}</div></div>
                @endif
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><div><h2>Related work orders</h2><div class="table-sub">Maintenance and field activity</div></div></div>
            @if ($asset->workOrders->isEmpty())
                <div class="empty">No work has been linked to this asset.</div>
            @else
                <div class="timeline">
                    @foreach ($asset->workOrders as $workOrder)
                        <div class="timeline-item">
                            <a class="timeline-title" href="{{ route('work-orders.show', $workOrder) }}">{{ $workOrder->work_order_number }} · {{ $workOrder->title }}</a>
                            <div class="timeline-note">{{ ucfirst($workOrder->category) }} · {{ str_replace('_', ' ', $workOrder->status) }}</div>
                            <div class="timeline-note">{{ $workOrder->assignedPersonnel->pluck('name')->join(', ') ?: 'Unassigned' }}</div>
                            <div class="timeline-time">{{ $workOrder->completed_at?->format('M j, Y') ?? $workOrder->created_at->format('M j, Y') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection