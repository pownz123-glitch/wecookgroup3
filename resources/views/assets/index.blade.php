@extends('layouts.app')

@section('title', 'Asset register')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px">Operations and engineering</div>
            <h1>Asset register</h1>
            <p class="subtitle">Electrical equipment linked to field work and maintenance activity.</p>
        </div>
        @if (in_array(auth()->user()->role, ['admin', 'operations'], true))
            <a class="btn btn-primary" href="{{ route('assets.create') }}"><span aria-hidden="true">＋</span> Register asset</a>
        @endif
    </div>

    <section class="panel">
        @if ($assets->isEmpty())
            <div class="empty"><strong>No assets registered</strong>Register an electrical asset to link it to field work.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Asset</th><th>Type</th><th>Serial number</th><th>Condition</th><th>Status</th><th>Work orders</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr>
                                <td><a class="table-title" href="{{ route('assets.show', $asset) }}">{{ $asset->asset_number }}</a><div class="table-sub">{{ $asset->name }}</div></td>
                                <td>{{ $asset->asset_type }}</td>
                                <td>{{ $asset->serial_number ?: '—' }}</td>
                                <td>{{ ucfirst($asset->condition) }}</td>
                                <td><span class="badge">{{ str_replace('_', ' ', $asset->status) }}</span></td>
                                <td>{{ $asset->work_orders_count }}</td>
                                <td><a class="btn btn-small" href="{{ route('assets.show', $asset) }}">History</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination"><span>{{ $assets->firstItem() }}–{{ $assets->lastItem() }} of {{ $assets->total() }}</span>{{ $assets->links() }}</div>
        @endif
    </section>
@endsection