@extends('layouts.app')

@section('title', 'New work order')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px"><a href="{{ route('work-orders.index') }}">Work orders</a> / New</div>
            <h1>Create work order</h1>
            <p class="subtitle">Set the work details, priority, asset, schedule, and assigned crew.</p>
        </div>
    </div>

    <form class="panel form-panel" method="POST" action="{{ route('work-orders.store') }}">
        @csrf
        <div class="form-grid">
            <div class="field field-wide">
                <label for="title">Work title</label>
                <input id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
            </div>
            <div class="field field-wide">
                <label for="description">Issue or work instructions</label>
                <textarea id="description" name="description" required>{{ old('description') }}</textarea>
            </div>
            <div class="field">
                <label for="category">Work category</label>
                <select id="category" name="category" required>
                    @foreach (['maintenance' => 'Maintenance', 'repair' => 'Repair', 'inspection' => 'Inspection', 'construction' => 'Construction', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    @foreach (['low', 'normal', 'high', 'urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', 'normal') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="asset_id">Related asset</label>
                <select id="asset_id" name="asset_id">
                    <option value="">No asset linked</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected((string) old('asset_id') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="scheduled_at">Scheduled date</label>
                <input id="scheduled_at" name="scheduled_at" type="date" value="{{ old('scheduled_at') }}">
            </div>
            <div class="field field-wide">
                <label for="assigned_personnel">Assign field personnel</label>
                <select id="assigned_personnel" name="assigned_personnel[]" multiple size="4">
                    @foreach ($fieldPersonnel as $person)
                        <option value="{{ $person->id }}" @selected(in_array((string) $person->id, old('assigned_personnel', []), true))>{{ $person->name }} · {{ $person->email }}</option>
                    @endforeach
                </select>
                @if ($fieldPersonnel->isEmpty())
                    <span class="table-sub">There are no active field personnel accounts to assign.</span>
                @endif
            </div>
        </div>
        <div class="form-actions">
            <a class="btn" href="{{ route('work-orders.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Create work order</button>
        </div>
    </form>
@endsection