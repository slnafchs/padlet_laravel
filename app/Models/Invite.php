<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'padlet_id', 'read', 'edit', 'Delete'];
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function padlet(): BelongsTo {
        return $this->belongsTo(Padlet::class);
    }
}
