<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

//Eloquent-Modell, das die Datenbanktabelle für Kommentare repräsentiert
class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'entrie_id', 'comment'];

    //Kommentar gehört zu einem Padlet
    public function entrie() : BelongsTo {
        return $this->BelongsTo(Entrie::class);
    }

    //User bzw. Ersteller gehört zu einem Padlet
    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }
}
