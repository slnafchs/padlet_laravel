<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    //sucht nach einem Benutzer (User) anhand der übergebenen ID und ruft zusätzlich dessen Einträge, Benutzerrechte,
    //Padlets, Bewertungen und Kommentare ab
    public function findById(string $id): JsonResponse
    {
        $user = User::where('id', $id)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        //Wenn der Benutzer gefunden wird, werden seine Daten als JSON-Antwort zurückgegeben, ansonsten null
        return $user != null ? response()->json($user, 200) : response()->json(null, 200);
    }

    //sucht nach einem Benutzer (User) anhand der übergebenen E-Mail-Adresse und ruft zusätzlich dessen Einträge,
    //Benutzerrechte, Padlets, Bewertungen und Kommentare ab
    public function findByEmail(string $mail): JsonResponse
    {
        $user = User::where('email', $mail)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        //Wenn der Benutzer gefunden wird, werden seine Daten als JSON-Antwort zurückgegeben, ansonsten null
        return $user != null ? response()->json($user, 200) : response()->json(null, 200);
    }

    //nimmt eine Anfrage entgegen, parst sie und erstellt einen neuen Benutzer in der Datenbank
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            $user = User::create($request->all());
            DB::commit();
            //Wenn die Erstellung erfolgreich ist, wird der erstellte Benutzer als JSON-Antwort zurückgegeben
            return response()->json($user, 201);
        } catch (\Exception $e) {
            //Falls ein Fehler auftritt, wird eine Fehlermeldung mit dem HTTP-Statuscode 420 zurückgegeben,
            //und alle Datenbankänderungen werden rückgängig gemacht (rollback), um Dateninkonsistenzen zu verhindern
            DB::rollBack();
            return response()->json("saving users failed: " . $e->getMessage(), 420);
        }
    }

    //nimmt eine Anfrage (Request) und eine Benutzer-ID entgegen.
    public function update(Request $request, string $user_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            //sucht den Benutzer mit der entsprechenden
            //ID in der Datenbank und ruft zusätzlich seine Beziehungen zu den Entitäten "entries", "userrights", "padlets",
            //"ratings" und "comments" ab
            $user = User::with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])
                ->where('id', $user_id)
                ->first();
            //Wenn der Benutzer gefunden wird, werden die in der Anfrage enthaltenen Daten analysiert und zur
            //Aktualisierung des Benutzers verwendet
            if ($user != null) {
                $request = $this->parseRequest($request);
                $user->firstName =$request['firstName'];
                $user->lastName =$request['lastName'];
                $user->email = $request['email'];
                $user->password = $request['password'];
                $user->image =$request['image'];
                //User wird gespeichert
                $user->save();
            }
            DB::commit();
            $user1 = User::with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])
                ->where('id', $user_id)
                ->first();
            //aktualisierte Benutzer wird als JSON-Antwort mit dem HTTP-Statuscode 201 zurückgegebe
            return response()->json($user1, 201);
            //Falls ein Fehler auftritt -> Fehlermeldung
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Updating user failed: " . $e->getMessage(), 420);
        }
    }

    //löscht USer nimmt eine Benutzer-ID entgegen
    public function delete(string $user_id): JsonResponse
    {
        //sucht den entsprechenden Benutzer in der Datenbank und ruft zusätzlich seine
        //Beziehungen zu den Entitäten "entries", "userrights", "padlets", "ratings" und "comments" ab
        $user = User::where('id', $user_id)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        //Wenn der Benutzer gefunden wird, wird er aus der Datenbank gelöscht,
        if ($user != null) {
            $user->delete();
            //JSON-Antwort mit dem HTTP-Statuscode 200 zurückgegeben, die den erfolgreichen Löschvorgang bestätigt
            return response()->json('$user (' .  $user_id . ') successfully deleted', 200);
        } else
            //Falls der Benutzer nicht gefunden wird, wird eine JSON-Antwort mit dem HTTP-Statuscode 422 zurückgegeben,
            //die angibt, dass der Löschvorgang nicht durchgeführt werden konnte, da der Benutzer nicht existiert.
            return response()->json('$user could not be deleted - it does not exist', 422);
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
