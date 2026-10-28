<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceDoc extends Model
{
    protected $fillable = ['title', 'body', 'topic'];
}
