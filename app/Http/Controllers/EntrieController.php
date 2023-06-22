<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Padlet;
use App\Models\Entrie;
use App\Models\User;
use App\Models\Userright;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent;
use Illuminate\Support\Facades\DB;

class EntrieController extends Controller
{

    //ruft alle Einträge ("Entrie") ab und lädt die zugehörigen Kommentare, Benutzer und Bewertungen. Die Funktion gibt eine
    //JSON-Antwort mit den abgerufenen Einträgen zurück.
    public function index():JsonResponse{
        $entry = Entrie::with(['comments', 'user', 'ratings'])->get();
        return response()->json($entry, 200);
    }

    //sucht nach einem Eintrag mit der angegebenen ID und lädt die zugehörigen Kommentare, Benutzer und Bewertungen
    public function findById(string $id): JsonResponse
    {
        $entrie = Entrie::where('id', $id)
            ->with(['comments', 'user', 'ratings'])->first();
        //wenn Eintrag gefunden wird, wird eine JSON-Antwort mit Eintrag zurückgegeben, ansonsten null
        return $entrie != null ? response()->json($entrie, 200) : response()->json(null, 200);
    }

    //sucht nach Einträgen mit der angegebenen Padlet-ID und lädt die zugehörigen Kommentare, Benutzer und Bewertungen
    public function findByPadletID(string $id):JsonResponse{
        $entry = Entrie::where('padlet_id', $id)
            ->with(['comments','user', 'ratings'])->get();
        //Wenn Einträge gefunden werden, wird eine JSON-Antwort mit den gefundenen Einträgen zurückgegeben, ansonsten null
        return $entry != null ? response()->json($entry, 200) : response()->json(null, 200);
    }

    //speichert einen neuen Eintrag basierend auf den übergebenen Daten
    public function save(Request $request, string $padletID):JsonResponse{
        $request = $this->parseRequest($request);
        DB::beginTransaction();
        try{
            //wenn alle erforderlichen Daten vorhanden sind, wird ein neuer Eintrag erstellt und in der
            //Datenbank gespeichert
            if(isset($request['user_id']) && isset($request['title']) &&isset($request['content']));
            {
                $entry = Entrie::create(
                    [
                        'user_id'=>$request['user_id'],
                        'title'=>$request['title'],
                        'content'=> $request['content'],
                        'padlet_id'=> $padletID
                    ]
                );
            }
            DB::commit();
            // Nach erfolgreicher Speicherung wird eine JSON-Antwort mit dem gespeicherten Eintrag zurückgegeben
            return response()->json($entry,200);
        }
        catch(\Exception $e) {
            DB::rollBack();
            // Bei einem Fehler während des Speicherns wird eine Fehlermeldung als JSON-Antwort zurückgegeben
            return response()->json("saving entrie failed: " . $e->getMessage(),420);
        }
    }

    //löscht einen Eintrag basierend auf der übergebenen ID
    public function delete(string $id): JsonResponse
    {
        $entrie = Entrie::where('id', $id)->first();
        if ($entrie != null) {
            $entrie->delete();
            //Wenn der Eintrag erfolgreich gelöscht wird, wird eine Erfolgsmeldung als JSON-Antwort mit dem
            //entsprechenden Statuscode zurückgegeben
            return response()->json('entry (' . $id . ') successfully deleted', 200);
        } else
            //Andernfalls wird eine Fehlermeldung zurückgegeben, falls der Eintrag nicht existiert
            return response()->json('entry could not be deleted - it does not exist', 422);
    }

    //aktualisiert einen Eintrag basierend auf der übergebenen ID und den Daten im Request-Objekt
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            //Der Eintrag wird mit den zugehörigen Kommentaren, Benutzern und Bewertungen abgerufen
            $entry = Entrie::with(['comments','user', 'ratings'])
                ->where('id', $id)->first();
            //es wird überprüft, ob der Eintrag ($entry) existiert. Wenn ja, werden die Daten im Request-Objekt
            //analysiert und der Eintrag mit den aktualisierten Daten aktualisiert
            if ($entry != null) {
                $request = $this->parseRequest($request);
                $entry->update($request->all());

                //Anschließend wird überprüft, ob im Request-Objekt ein Eintragsarray mit dem Schlüssel "entries"
                //existiert und ob es sich um ein Array handelt
                if (isset($request['entries']) && is_array($request['entries'])) {
                    //wenn ja, wird eine Schleife durchlaufen, um jeden Eintrag im Array zu verarbeiten.
                    foreach ($request['entries'] as $e) {
                        //Für jeden Eintrag wird geprüft, ob bereits ein Eintrag mit dem Titel, Inhalt, Benutzer-ID
                        //und dem angegebenen Padlet-ID-Wert existiert
                        $entrie = Entrie::firstOrNew(['title' => $e['title'], 'content' => $e['content'], 'user_id' => $e['user_id'], 'padlet_id'=>$id]);
                        //Wenn ein solcher Eintrag nicht vorhanden ist, wird ein neuer Eintrag erstellt und dem aktuellen Eintrag hinzugefügt
                        $entry->entries()->save($entrie);
                    }
                }
                //aktualisierter Eintrag wird gespeichert
                $entry->save();
            }
            DB::commit();
            $entry1 = Entrie::with(['comments','user', 'ratings'])
                ->where('id', $id)->first();
            //Bei erfolgreicher Aktualisierung wird der aktualisierte Eintrag als JSON-Antwort mit dem entsprechenden
            //Statuscode zurückgegeben
            return response()->json($entry1, 201);
        } catch (\Exception $e) {
            //Andernfalls wird eine Fehlermeldung mit entsprechendem Statuscode zurückgegeben
            DB::rollBack();
            return response()->json("updating Entry failed: " . $e->getMessage(), 420);
        }
    }

    //nimmt eine Anfrage (Request) entgegen und analysiert sie
    private function parseRequest(Request $request): Request
    {
        //erstellt ein neues DateTime-Objekt basierend auf dem Wert des 'created_at'-Feldes in der Anfrage
        $date = new \DateTime($request->created_at);
        //aktualisiert den Wert in der Anfrage
        $request['published'] = $date;
        return $request;
    }
}
