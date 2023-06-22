<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrie extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'padlet_id','title', 'content'];

    //Ein Entrie hat mehrere Bewertungen
    public function ratings() : HasMany {
        return $this->HasMany(Rating::class);
    }

    //Ein Entrie hat mehrere Kommentare
    public function comments() : HasMany {
        return $this->HasMany(Comment::class);
    }

    //Ein Entrie gehört einem User
    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }

    //Ein Entrie gehört zu einem Padlet
    public function padlet() : BelongsTo {
        return $this->BelongsTo(Padlet::class);
    }
}
