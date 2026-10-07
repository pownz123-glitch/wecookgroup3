<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', WorkOrder::class);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'assigned', 'in_progress', 'for_review', 'completed'])],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = WorkOrder::query()->with(['asset', 'assignedPersonnel']);

        if ($request->user()->role === 'field') {
            $query->whereHas('assignedPersonnel', fn ($builder) => $builder->whereKey($request->user()->id));
        }

        $workOrders = $query
            ->when($filters['status'] ?? null, fn ($builder, $status) => $builder->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($builder, $priority) => $builder->where('priority', $priority))
            ->when($filters['q'] ?? null, function ($builder, $search): void {
                $builder->where(fn ($query) => $query
                    ->where('work_order_number', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('work-orders.index', compact('workOrders', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', WorkOrder::class);
        $assets = Asset::query()->whereNot('status', 'retired')->orderBy('asset_number')->get();
        $fieldPersonnel = User::query()->where('role', 'field')->where('is_active', true)->orderBy('name')->get();

        return view('work-orders.create', compact('assets', 'fieldPersonnel'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', WorkOrder::class);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => [
                'required',
                Rule::in(['maintenance', 'repair', 'inspection', 'construction', 'other']),
            ],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_personnel' => ['nullable', 'array'],
            'assigned_personnel.*' => [Rule::exists('users', 'id')->where('role', 'field')->where('is_active', true)],
        ]);

        $personnelIds = $data['assigned_personnel'] ?? [];
        $workOrder = WorkOrder::create([
            'work_order_number' => 'WO-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'asset_id' => $data['asset_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => count($personnelIds) > 0 ? 'assigned' : 'pending',
            'created_by' => $request->user()->id,
            'assigned_by' => count($personnelIds) > 0 ? $request->user()->id : null,
            'date_assigned' => count($personnelIds) > 0 ? now() : null,
        ]);

        if (count($personnelIds) > 0) {
            $workOrder->assignedPersonnel()->attach(array_fill_keys($personnelIds, ['assigned_at' => now()]));
        }

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Work order created.');
    }

    public function show(WorkOrder $workOrder): View
    {
        Gate::authorize('view', $workOrder);
        $workOrder->load(['asset', 'creator', 'assignedPersonnel', 'updates.user']);
        $fieldPersonnel = in_array(auth()->user()->role, ['admin', 'supervisor'], true)
            ? User::query()->where('role', 'field')->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('work-orders.show', compact('workOrder', 'fieldPersonnel'));
    }

    public function assign(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('assign', $workOrder);
        abort_unless(in_array($workOrder->status, ['pending', 'assigned'], true), 409);
        $data = $request->validate([
            'assigned_personnel' => ['required', 'array', 'min:1'],
            'assigned_personnel.*' => [Rule::exists('users', 'id')->where('role', 'field')->where('is_active', true)],
        ]);

        DB::transaction(function () use ($data, $request, $workOrder): void {
            $workOrder->assignedPersonnel()->sync(array_fill_keys($data['assigned_personnel'], ['assigned_at' => now()]));
            $workOrder->update([
                'status' => 'assigned',
                'assigned_by' => $request->user()->id,
                'date_assigned' => now(),
            ]);
            $workOrder->updates()->create([
                'user_id' => $request->user()->id,
                'update_type' => 'assignment',
                'notes' => 'Field personnel assigned.',
            ]);
        });

        return back()->with('status', 'Field personnel assigned.');
    }

    public function start(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('start', $workOrder);
        DB::transaction(function () use ($request, $workOrder): void {
            $workOrder->update(['status' => 'in_progress']);
            if ($workOrder->asset && $workOrder->asset->status !== 'retired') {
                $workOrder->asset->update(['status' => 'under_maintenance']);
            }
            $this->recordUpdate($workOrder, $request->user(), 'status', 'Work started.');
        });

        return back()->with('status', 'Work started.');
    }

    public function updateFieldWork(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('update', $workOrder);
        $data = $request->validate([
            'work_performed' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $workOrder->updates()->create([
            'user_id' => $request->user()->id,
            'update_type' => 'field_update',
            'work_performed' => $data['work_performed'] ?? null,
            'notes' => $data['notes'] ?? null,
            'photo_path' => $request->file('photo')?->store('work-order-photos', 'public'),
        ]);

        return back()->with('status', 'Field update recorded.');
    }

    public function submit(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('submit', $workOrder);
        $data = $request->validate([
            'work_performed' => ['required', 'string', 'max:5000'],
            'field_notes' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($data, $request, $workOrder): void {
            $photoPath = $request->file('photo')?->store('work-order-photos', 'public');
            $workOrder->update([
                'status' => 'for_review',
                'work_performed' => $data['work_performed'],
                'field_notes' => $data['field_notes'] ?? null,
                'completion_photo_path' => $photoPath,
            ]);
            $workOrder->updates()->create([
                'user_id' => $request->user()->id,
                'update_type' => 'submission',
                'work_performed' => $data['work_performed'],
                'notes' => $data['field_notes'] ?? null,
                'photo_path' => $photoPath,
                'submitted_at' => now(),
            ]);
        });

        return back()->with('status', 'Work submitted for supervisor review.');
    }

    private function recordUpdate(WorkOrder $workOrder, User $user, string $type, string $notes): void
    {
        $workOrder->updates()->create([
            'user_id' => $user->id,
            'update_type' => $type,
            'notes' => $notes,
        ]);
    }
}
