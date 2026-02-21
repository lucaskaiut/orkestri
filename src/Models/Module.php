<?php

namespace LucasKaiut\Orkestri\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = ['name', 'description'];

    public function fields(): HasMany
    {
        return $this->hasMany(ModuleField::class);
    }
}