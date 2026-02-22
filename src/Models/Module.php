<?php

namespace LucasKaiut\Orkestri\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = ['name', 'description', 'migration_status'];

    public function fields(): HasMany
    {
        return $this->hasMany(ModuleField::class);
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(ModuleRelationship::class);
    }
}
