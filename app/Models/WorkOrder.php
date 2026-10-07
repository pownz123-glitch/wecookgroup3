<?php

namespace App\Models;

use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'work_order_number', 'title', 'description', 'category', 'priority', 'status',
        'asset_id', 'created_by', 'assigned_by', 'approved_by', 'scheduled_at', 'date_assigned',
        'work_performed', 'field_notes', 'completion_photo_path', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'date_assigned' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function assignedPersonnel(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('assigned_at')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(WorkOrderUpdate::class)->latest();
    }
}
