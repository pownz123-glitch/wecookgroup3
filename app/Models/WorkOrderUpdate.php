<?php

namespace App\Models;

use Database\Factories\WorkOrderUpdateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderUpdate extends Model
{
    /** @use HasFactory<WorkOrderUpdateFactory> */
    use HasFactory;

    protected $fillable = [
        'work_order_id', 'user_id', 'update_type', 'work_performed', 'notes', 'photo_path', 'submitted_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
