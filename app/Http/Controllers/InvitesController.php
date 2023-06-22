<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitesController extends Controller
{
    //ruft alle Einladungen (Invites) aus der Datenbank ab und lädt dabei die zugehörigen Benutzer (User) und Padlets (Padlet)
    public function index():JsonResponse{
        $invites = Invite::with(['user', 'padlet'])->get();
        return response()->json($invites, 200);
    }

    //speichert eine neue Einladung (Invite) in der Datenbank
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            //Wenn das Speichern erfolgreich ist, wird die Einladung zurückgegeben und die Transaktion wird bestätigt
            $invites = Invite::create($request->all());
            DB::commit();
            return response()->json($invites, 201);
        } catch (\Exception $e) {
            //Andernfalls wird die Transaktion zurückgesetzt und eine Fehlermeldung mit dem entsprechenden Statuscode zurückgegeben
            DB::rollBack();
            return response()->json("saving invite failed: " . $e->getMessage(), 420);
        }
    }

    //sucht nach Einladungen (Invites), die einem bestimmten Benutzer (user_id) zugeordnet sind
    public function findByUserId(string $user_id) : JsonResponse
    {
        //uft die entsprechenden Einladungen aus der Datenbank ab und gibt sie zusammen mit den zugehörigen Benutzern
        //und Padlets als JSON zurück
        $invites = Invite::where('user_id', $user_id)
            ->with(['user', 'padlet'])->get();
        return $invites != null ? response()->json($invites, 200) : response()->json(null, 200);
    }

    //sucht nach einer Einladung (Invite) anhand der übergebenen Padlet-ID und Benutzer-ID
    public function findIfExists(string $padlet_id, string $user_id) : JsonResponse
    {
        //Wenn eine Einladung gefunden wird, wird sie zusammen mit den zugehörigen Benutzer- und Padlet-Daten als
        //JSON-Antwort zurückgegeben
        $invites = Invite::where('user_id', $user_id)->where('padlet_id', $padlet_id)
            ->with(['user', 'padlet'])->first();
        return $invites != null ? response()->json($invites, 200) : response()->json(null, 200);
    }

    //aktualisiert eine Einladung (Invite) anhand der übergebenen ID
    public function update(Request $request, string $id): JsonResponse
    {
        //Die Aktualisierung erfolgt innerhalb einer Transaktion, um Datenintegrität sicherzustellen.
        DB::beginTransaction();
        try {
            //die Einladung wird abgerufen und anschließend die übergebenen Daten aus dem Request darauf angewendet
            $invite = Invite::with(['user', 'padlet'])
                ->where('id', $id)
                ->first();

            //Wenn die Einladung gefunden und aktualisiert wurde, wird sie zusammen mit den zugehörigen Benutzer-
            //und Padlet-Daten als JSON-Antwort zurückgegebe
            if ($invite != null) {
                $request = $this->parseRequest($request);
                $invite->user_id =$request['user_id'];
                $invite->padlet_id =$request['padlet_id'];
                $invite->read = $request['read'];
                $invite->edit = $request['edit'];
                $invite->Delete =$request['Delete'];
                $invite->save();
            }
            DB::commit();
            $invite1 = Invite::with(['user', 'padlet'])
                ->where('id', $id)
                ->first();
            return response()->json($invite1, 201);
            //Wenn ein Fehler auftritt, wird eine Fehlermeldung mit dem entsprechenden Fehlercode zurückgegeben.
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Updating invite failed: " . $e->getMessage(), 420);
        }
    }

    //löscht eine Einladung (Invite) anhand der übergebenen ID
    public function delete(string $id): JsonResponse
    {
        //Einladung abgerufen und wenn sie existiert, wird sie gelöscht
        $invite = Invite::where('id', $id)
            ->with(['user', 'padlet'])->first();
        if ($invite != null) {
            $invite->delete();
            // Eine entsprechende JSON-Antwort wird zurückgegeben, die den Erfolg der Löschung
            return response()->json('$invite (' . $id . ') successfully deleted', 200);
        } else
            //Error falls es Invite nicht gibt
            return response()->json('$invite could not be deleted - it does not exist', 422);
    }


    //erstellt ein neues DateTime-Objekt basierend auf dem Wert des 'published'-Feldes in der Anfrage
    private function parseRequest(Request $request): Request
    {
        //erstellt ein neues DateTime-Objekt basierend auf dem Wert des 'published'-Feldes in der Anfrage
        $date = new \DateTime($request->published);
        //aktualisiert den Wert in der Anfrage
        $request['published'] = $date;
        return $request;
    }
}
