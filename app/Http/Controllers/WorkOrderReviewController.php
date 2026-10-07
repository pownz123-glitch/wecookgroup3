<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class WorkOrderReviewController extends Controller
{
    public function approve(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('review', $workOrder);
        abort_unless($workOrder->status === 'for_review', 409);

        DB::transaction(function () use ($request, $workOrder): void {
            $workOrder->update([
                'status' => 'completed',
                'approved_by' => $request->user()->id,
                'completed_at' => now(),
            ]);
            if ($workOrder->asset?->status === 'under_maintenance') {
                $workOrder->asset->update(['status' => 'active']);
            }
            $workOrder->updates()->create([
                'user_id' => $request->user()->id,
                'update_type' => 'approved',
                'notes' => 'Work approved and closed by supervisor.',
            ]);
        });

        return back()->with('status', 'Work order approved and closed.');
    }

    public function returnForCorrection(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('review', $workOrder);
        abort_unless($workOrder->status === 'for_review', 409);
        $data = $request->validate(['correction_notes' => ['required', 'string', 'max:3000']]);

        DB::transaction(function () use ($data, $request, $workOrder): void {
            $workOrder->update(['status' => 'in_progress']);
            $workOrder->updates()->create([
                'user_id' => $request->user()->id,
                'update_type' => 'returned',
                'notes' => $data['correction_notes'],
            ]);
        });

        return back()->with('status', 'Work returned to the assigned field personnel.');
    }
}
