@extends('layouts.app')

@section('title', 'Edit '.$asset->asset_number)

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow" style="margin-bottom: 9px"><a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_number }}</a> / Edit</div>
            <h1>Edit asset record</h1>
            <p class="subtitle">Update equipment details, condition, and current operating status.</p>
        </div>
    </div>

    <form class="panel form-panel" method="POST" action="{{ route('assets.update', $asset) }}">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div class="field"><label for="asset_number">Asset ID</label><input id="asset_number" name="asset_number" value="{{ old('asset_number', $asset->asset_number) }}" required maxlength="50"></div>
            <div class="field">
                <label for="asset_type">Asset type</label>
                <select id="asset_type" name="asset_type" required>
                    @foreach (['Transformer', 'Electric meter', 'Distribution equipment', 'Cable', 'Maintenance equipment', 'Other'] as $type)
                        <option value="{{ $type }}" @selected(old('asset_type', $asset->asset_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="name">Name / description</label><input id="name" name="name" value="{{ old('name', $asset->name) }}" required maxlength="255"></div>
            <div class="field"><label for="serial_number">Serial number</label><input id="serial_number" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" maxlength="100"></div>
            <div class="field">
                <label for="status">Current status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'under_maintenance', 'damaged', 'retired'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $asset->status) === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="condition">Condition</label>
                <select id="condition" name="condition" required>
                    @foreach (['good', 'fair', 'poor', 'critical'] as $condition)
                        <option value="{{ $condition }}" @selected(old('condition', $asset->condition) === $condition)>{{ ucfirst($condition) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="installation_date">Installation / acquisition date</label><input id="installation_date" name="installation_date" type="date" value="{{ old('installation_date', $asset->installation_date?->format('Y-m-d')) }}"></div>
            <div class="field field-wide"><label for="description">Additional details</label><textarea id="description" name="description">{{ old('description', $asset->description) }}</textarea></div>
        </div>
        <div class="form-actions">
            <a class="btn" href="{{ route('assets.show', $asset) }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Save changes</button>
        </div>
    </form>
@endsection