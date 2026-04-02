<?php

namespace ApurbaLabs\IAM\Models;

use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    protected $guarded = [];
    
    protected $table = 'iam_scopes';
}

