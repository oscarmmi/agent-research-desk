<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchStep extends Model
{
    protected $fillable = ['research_job_id', 'tool_name', 'input', 'output', 'position'];

    /** @return BelongsTo<ResearchJob, $this> */
    public function researchJob(): BelongsTo
    {
        return $this->belongsTo(ResearchJob::class);
    }
}
