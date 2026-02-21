<?php

namespace LucasKaiut\Orkestri\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleField extends Model
{
    protected $fillable = ['name', 'label', 'type', 'module_id', 'nullable', 'required', 'default'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}