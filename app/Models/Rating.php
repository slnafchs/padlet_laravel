<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'entrie_id', 'rating'];

    public function entrie() : BelongsTo {
        return $this->BelongsTo(Entrie::class);
    }

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }
}
