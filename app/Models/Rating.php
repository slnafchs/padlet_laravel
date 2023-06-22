<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

//Eloquent-Modell, das die Datenbanktabelle für Bewertungen repräsentiert
class Rating extends Model
{
    //Verwendung von Factories in Eloquent-Modellen wird ermöglicht.
    //Es bietet eine standardisierte Methode zur Erzeugung von Testdaten oder Dummy-Daten für das Modell.
    use HasFactory;
    //ausfüllbare Felder
    protected $fillable = ['user_id', 'entrie_id', 'rating'];

    protected $primaryKey = ['user_id', 'entrie_id']; //Primärschlüssel
    public $incrementing = false;

    //Eine Bewertung gehört zu einem Entrie
    public function entrie() : BelongsTo {
        return $this->BelongsTo(Entrie::class);
    }

    //Eine Bewertung gehört zu einem User
    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
