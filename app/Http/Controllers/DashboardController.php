<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->is_active, 403);

        $workOrderQuery = WorkOrder::query();

        if ($user->role === 'field') {
            $workOrderQuery->whereHas('assignedPersonnel', fn ($query) => $query->whereKey($user->id));
        }

        $stats = [
            'total' => (clone $workOrderQuery)->count(),
            'pending' => (clone $workOrderQuery)->where('status', 'pending')->count(),
            'assigned' => (clone $workOrderQuery)->where('status', 'assigned')->count(),
            'in_progress' => (clone $workOrderQuery)->where('status', 'in_progress')->count(),
            'for_review' => (clone $workOrderQuery)->where('status', 'for_review')->count(),
            'completed' => (clone $workOrderQuery)->where('status', 'completed')->count(),
            'urgent' => (clone $workOrderQuery)->where('priority', 'urgent')->whereNot('status', 'completed')->count(),
        ];

        $workOrders = (clone $workOrderQuery)->with(['asset', 'assignedPersonnel'])->latest()->limit(8)->get();
        $assetCount = in_array($user->role, ['admin', 'operations', 'supervisor'], true) ? Asset::count() : null;

        return view('dashboard', compact('stats', 'workOrders', 'assetCount'));
    }
}
