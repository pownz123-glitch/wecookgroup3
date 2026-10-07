<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Asset::class);
        $assets = Asset::query()->withCount('workOrders')->orderBy('asset_number')->paginate(15);

        return view('assets.index', compact('assets'));
    }

    public function create(): View
    {
        Gate::authorize('create', Asset::class);

        return view('assets.create');
    }

    public function edit(Asset $asset): View
    {
        Gate::authorize('update', $asset);

        return view('assets.edit', compact('asset'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Asset::class);
        $data = $request->validate([
            'asset_number' => ['required', 'string', 'max:50', 'unique:assets,asset_number'],
            'asset_type' => [
                'required',
                Rule::in(['Transformer', 'Electric meter', 'Distribution equipment', 'Cable', 'Maintenance equipment', 'Other']),
            ],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'under_maintenance', 'damaged', 'retired'])],
            'condition' => ['required', Rule::in(['good', 'fair', 'poor', 'critical'])],
            'installation_date' => ['nullable', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $asset = Asset::create($data);

        return redirect()->route('assets.show', $asset)->with('status', 'Asset registered.');
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('update', $asset);
        $data = $request->validate([
            'asset_number' => ['required', 'string', 'max:50', Rule::unique('assets')->ignore($asset)],
            'asset_type' => [
                'required',
                Rule::in(['Transformer', 'Electric meter', 'Distribution equipment', 'Cable', 'Maintenance equipment', 'Other']),
            ],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'under_maintenance', 'damaged', 'retired'])],
            'condition' => ['required', Rule::in(['good', 'fair', 'poor', 'critical'])],
            'installation_date' => ['nullable', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $asset->update($data);

        return redirect()->route('assets.show', $asset)->with('status', 'Asset record updated.');
    }

    public function show(Asset $asset): View
    {
        Gate::authorize('view', $asset);
        $workOrders = $asset->workOrders()->with('assignedPersonnel')->latest();

        if (auth()->user()->role === 'field') {
            $workOrders->whereHas('assignedPersonnel', fn ($query) => $query->whereKey(auth()->id()));
        }

        $asset->setRelation('workOrders', $workOrders->get());

        return view('assets.show', compact('asset'));
    }
}
