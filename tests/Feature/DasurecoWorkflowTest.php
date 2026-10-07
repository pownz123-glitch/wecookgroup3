<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DasurecoWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_field_personnel_cannot_view_an_unassigned_work_order(): void
    {
        $fieldPersonnel = User::factory()->create(['role' => 'field']);
        $workOrder = WorkOrder::factory()->create();

        $this->actingAs($fieldPersonnel)
            ->get('/work-orders/'.$workOrder->id)
            ->assertForbidden();
    }

    public function test_field_personnel_only_see_assigned_work_in_asset_history(): void
    {
        $fieldPersonnel = User::factory()->create(['role' => 'field']);
        $otherFieldPersonnel = User::factory()->create(['role' => 'field']);
        $asset = Asset::factory()->create();
        $assignedWorkOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'title' => 'Assigned transformer inspection',
        ]);
        $otherWorkOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'title' => 'Restricted cable repair',
        ]);
        $assignedWorkOrder->assignedPersonnel()->attach($fieldPersonnel->id, ['assigned_at' => now()]);
        $otherWorkOrder->assignedPersonnel()->attach($otherFieldPersonnel->id, ['assigned_at' => now()]);

        $this->actingAs($fieldPersonnel)
            ->get(route('assets.show', $asset))
            ->assertOk()
            ->assertSee('Assigned transformer inspection')
            ->assertDontSee('Restricted cable repair');
    }

    public function test_supervisor_can_create_a_work_order_linked_to_an_asset_and_field_crew(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $fieldPersonnel = User::factory()->create(['role' => 'field']);
        $asset = Asset::factory()->create();

        $response = $this->actingAs($supervisor)->post(route('work-orders.store'), [
            'title' => 'Inspect feeder connection',
            'description' => 'Check connection temperature and insulation.',
            'category' => 'inspection',
            'priority' => 'high',
            'asset_id' => $asset->id,
            'assigned_personnel' => [$fieldPersonnel->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('work_orders', [
            'title' => 'Inspect feeder connection',
            'asset_id' => $asset->id,
            'status' => 'assigned',
            'created_by' => $supervisor->id,
        ]);
        $this->assertDatabaseHas('user_work_order', [
            'user_id' => $fieldPersonnel->id,
            'assigned_at' => now()->toDateTimeString(),
        ]);
    }

    public function test_assigned_field_personnel_can_start_and_submit_work_for_review(): void
    {
        $fieldPersonnel = User::factory()->create(['role' => 'field']);
        $asset = Asset::factory()->create();
        $workOrder = WorkOrder::factory()->create(['status' => 'assigned', 'asset_id' => $asset->id]);
        $workOrder->assignedPersonnel()->attach($fieldPersonnel->id, ['assigned_at' => now()]);

        $this->actingAs($fieldPersonnel)
            ->post(route('work-orders.start', $workOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'under_maintenance']);

        $this->post(route('work-orders.submit', $workOrder), [
            'work_performed' => 'Replaced the damaged connector and tested the line.',
            'field_notes' => 'Service restored and verified.',
        ])->assertRedirect();

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'for_review',
            'work_performed' => 'Replaced the damaged connector and tested the line.',
        ]);
        $this->assertDatabaseHas('work_order_updates', [
            'work_order_id' => $workOrder->id,
            'user_id' => $fieldPersonnel->id,
            'update_type' => 'submission',
        ]);
    }

    public function test_supervisor_can_approve_and_close_a_submitted_work_order(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $asset = Asset::factory()->create(['status' => 'under_maintenance']);
        $workOrder = WorkOrder::factory()->create(['status' => 'for_review', 'asset_id' => $asset->id]);

        $this->actingAs($supervisor)
            ->post(route('work-orders.approve', $workOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'completed',
            'approved_by' => $supervisor->id,
        ]);
        $this->assertDatabaseHas('work_order_updates', [
            'work_order_id' => $workOrder->id,
            'update_type' => 'approved',
        ]);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'active']);
    }

    public function test_field_personnel_cannot_approve_a_work_order(): void
    {
        $fieldPersonnel = User::factory()->create(['role' => 'field']);
        $workOrder = WorkOrder::factory()->create(['status' => 'for_review']);

        $this->actingAs($fieldPersonnel)
            ->post(route('work-orders.approve', $workOrder))
            ->assertForbidden();

        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id, 'status' => 'for_review']);
    }

    public function test_operations_staff_can_register_assets(): void
    {
        $operationsStaff = User::factory()->create(['role' => 'operations']);

        $this->actingAs($operationsStaff)
            ->post(route('assets.store'), [
                'asset_number' => 'AST-00123',
                'asset_type' => 'Transformer',
                'name' => 'Substation transformer 02',
                'status' => 'active',
                'condition' => 'good',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assets', [
            'asset_number' => 'AST-00123',
            'name' => 'Substation transformer 02',
        ]);
    }

    public function test_operations_staff_can_update_asset_condition_and_status(): void
    {
        $operationsStaff = User::factory()->create(['role' => 'operations']);
        $asset = Asset::factory()->create();

        $this->actingAs($operationsStaff)
            ->put(route('assets.update', $asset), [
                'asset_number' => $asset->asset_number,
                'asset_type' => $asset->asset_type,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'status' => 'damaged',
                'condition' => 'poor',
                'description' => 'Requires inspection.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'damaged',
            'condition' => 'poor',
        ]);
    }
}
