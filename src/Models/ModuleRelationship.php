<?php

namespace LucasKaiut\Orkestri\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleRelationship extends Model
{
    protected $fillable = [
        'module_id',
        'type',
        'related_module',
        'foreign_key',
        'owner_key',
        'relation_name',
    ];

    protected $casts = [
        'module_id'      => 'integer',
        'related_module' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function relatedModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'related_module');
    }
}