<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchJob extends Model
{
    protected $fillable = ['question', 'brief', 'status'];

    /** @return HasMany<ResearchStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(ResearchStep::class)->orderBy('position');
    }
}
