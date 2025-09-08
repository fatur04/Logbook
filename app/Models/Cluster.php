<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    protected $fillable = [
        'cluster',
        'role',
        'aplikasi',
    ];
}
