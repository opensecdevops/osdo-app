<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    protected $fillable = [
        'user_id',
        'package_version_id',
        'platform',
        'namespace',
        'environment',
        'status',
        'message',
        'metadata',
        'deployed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'deployed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function packageVersion(): BelongsTo
    {
        return $this->belongsTo(PackageVersion::class);
    }
}
