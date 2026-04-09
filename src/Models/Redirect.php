<?php

namespace AltDesign\AltRedirect\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $table = 'alt_redirects';

    protected $fillable = [
        'id',
        'from',
        'to',
        'redirect_type',
        'sites',
    ];

    protected $casts = [
        'sites' => 'array',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}
