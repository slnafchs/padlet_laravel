<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Padlet;
use App\Models\Entrie;
use App\Models\User;
use App\Models\Userright;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    //sucht nach Bewertungen in der Datenbank, die mit einem bestimmten Eintrag (entry) verknüpft sind, basierend
    // auf der übergebenen Entrie ID
    public function findByEntryID(string $entry_id):JsonResponse{
        //ruft die Bewertungen zusammen mit den zugehörigen Benutzern und Einträgen ab
        $rating = Rating::where('entrie_id', $entry_id)
            ->with(['user', 'entrie'])->get();
        return $rating != null ? response()->json($rating, 200) : response()->json(null, 200);
    }

    //Diese Funktion speichert eine Bewertung in der Datenbank basierend auf den übergebenen Daten in der Anfrage
    //(Request) und der Entrie ID
    public function saveRating(Request $request, string $entrieID): JsonResponse
    {
        $request = $this->parseRequest($request);
        //Transaktion gestartet, um sicherzustellen, dass alle Datenbankoperationen entweder erfolgreich durchgeführt
        //oder bei einem Fehler rückgängig gemacht werden
        DB::beginTransaction();

        try {
            // überprüft, ob sowohl die Benutzer-ID als auch die Bewertung in der Anfrage vorhanden sind
            if(isset($request['user_id']) &&isset($request['rating']));
            {
                //neue Bewertung mit den entsprechenden Daten erstellt
                $rating = Rating::create(
                    [
                        'user_id'=>$request['user_id'],
                        'rating'=>$request['rating'],
                        'entrie_id'=> $entrieID
                    ]
                );
            }
            DB::commit();
            //wenn erfolgreich, wird Bewertung zurückgegeben
            return response()->json($rating, 201);
        } catch (\Exception $e) {
            //wenn nicht erfolgreich, dann Fehlermeldung
            DB::rollBack();
            return response()->json("saving rating failed: " . $e->getMessage(), 420);
        }
    }

    //Aktualisierung einer Bewertung in der Datenbank
    public function update(Request $request, string $entrie_id, string $user_id): JsonResponse
    {
        //Transaktion gestartet, um sicherzustellen, dass alle Datenbankoperationen entweder erfolgreich durchgeführt
        //oder bei einem Fehler rückgängig gemacht werden
        DB::beginTransaction();

        try {
            // es wird die vorhandene Bewertung basierend auf der Eintrags-ID und der Benutzer-ID abgerufen
            $rating = Rating::with(['user', 'entrie'])
                ->where('entrie_id', $entrie_id)
                ->where('user_id', $user_id)
                ->first();

            //Wenn eine Bewertung gefunden wird, werden die Daten aus der Anfrage aktualisiert
            //und die Bewertung gespeichert
            if ($rating != null) {
                $request = $this->parseRequest($request);
                $rating->user_id =$request['user_id'];
                $rating->entrie_id =$request['entrie_id'];
                $rating->rating =$request['rating'];
                $rating->save();
            }

            DB::commit();

            //neues Rating wird zurückgegeben, wenn erfolgreich
            $rating1 = Rating::with(['entrie', 'user'])
                ->where('entrie_id', $entrie_id)
                ->where('user_id', $user_id)
                ->first();

            return response()->json($rating1, 201);
        } catch (\Exception $e) {
            //nicht erfolgreich -> Fehlermeldung
            DB::rollBack();
            return response()->json("Updating rating failed: " . $e->getMessage(), 420);
        }
    }

    //Löschen einer Bewertung in der Datenbank basierend auf der Eintrags-ID und der Benutzer-ID
    public function delete(string $entrie_id, string $user_id): JsonResponse
    {
        //Bewertung wird abgerufen, die mit den angegebenen IDs verknüpft ist
        $rating = Rating::where('entrie_id', $entrie_id)->where('user_id', $user_id)
            ->with(['user', 'entrie'])->first();
        //Wenn eine Bewertung gefunden wird, wird sie gelöscht
        if ($rating != null) {
            $rating->delete();
            return response()->json('$rating (' . $entrie_id . " " . $user_id . ') successfully deleted', 200);
        } else
            //Wenn keine Bewertung mit den angegebenen IDs gefunden wird -> Fehlermeldung
            return response()->json('$rating could not be deleted - it does not exist', 422);
    }

    //nimmt eine Anfrage (Request) entgegen und analysiert sie
    private function parseRequest(Request $request): Request
    {
        //erstellt ein neues DateTime-Objekt basierend auf dem Wert des 'published'-Feldes in der Anfrage
        $date = new \DateTime($request->published);
        //aktualisiert den Wert in der Anfrage
        $request['published'] = $date;
        return $request;
    }
}
