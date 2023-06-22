<?php

namespace App\Http\Controllers;
use App\Models\Userright;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserrightsController extends Controller
{
    //ruft alle Userrights aus der Datenbank ab und lädt dabei die Beziehungen zu den Benutzern und Padlets
    public function index():JsonResponse{
        $userright = Userright::with(['user', 'padlet'])->get();
        //bgerufenen Userrights werden als JSON-Antwort zurückgegeben
        return response()->json($userright, 200);
    }

    //Speichere Userright
    //nimmt eine Anfrage (Request) entgegen, parst sie und erstellt ein neues Userright-Objekt in der Datenbank
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();
        //wennn Erstellung erfolgreich ist, wird das erstellte Userright als JSON-Antwort mit dem HTTP-Statuscode
        //201 zurückgegebe
        try {
            $userright = Userright::create($request->all());
            DB::commit();
            return response()->json($userright, 201);
        } catch (\Exception $e) {
            //Wenn ein Fehler auftritt, wird eine Fehlermeldung mit dem HTTP-Statuscode 420 zurückgegeben, und alle
            //Datenbankänderungen werden rückgängig gemacht (rollback).
            DB::rollBack();
            return response()->json("saving userright failed: " . $e->getMessage(), 420);
        }
    }

    //sucht nach einem Userright-Eintrag in der Datenbank anhand der übergebenen Padlet-ID und Benutzer-ID
    public function findById(string $padlet_id, string $user_id) : JsonResponse
    {
        $userright = Userright::where('padlet_id', $padlet_id)->where( 'user_id', $user_id)
            ->with(['user', 'padlet'])->first();
        //Wenn ein entsprechendes Userright gefunden wird, werden die zugehörigen Benutzer- und Padlet-Daten geladen
        //Wenn kein Userright gefunden wird, wird eine leere JSON-Antwort mit dem HTTP-Statuscode 200 zurückgegeben.
        return $userright != null ? response()->json($userright, 200) : response()->json(null, 200);
    }

    //nimmt eine Anfrage (Request) sowie eine Padlet-ID und Benutzer-ID entgegen
    public function update(Request $request, string $padlet_id, string $user_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            //ucht nach dem entsprechenden Userright-Eintrag in der Datenbank und lädt dabei die zugehörigen Padlet-
            //und Benutzerdaten
            $userright = Userright::with(['padlet', 'user'])
                ->where('padlet_id', $padlet_id)
                ->where('user_id', $user_id)
                ->first();

            //Wenn das Userright gefunden wird, werden die in der Anfrage enthaltenen Daten analysiert und zur
            //Aktualisierung des Userrights verwendet
            if ($userright != null) {
                $request = $this->parseRequest($request);
                //$userright->update($request->all());
                $userright->user_id =$request['user_id'];
                $userright->padlet_id =$request['padlet_id'];
                $userright->read =$request['read'];
                $userright->edit =$request['edit'];
                $userright->delete =$request['delete'];
                $userright->save();
            }

            //Änderungen werden in der Datenbank gespeichert, und das aktualisierte Userright wird als JSON-Antwort
            //mit dem HTTP-Statuscode 201 zurückgegeben
            DB::commit();
            $userright1 = Userright::with(['padlet', 'user'])
                ->where('padlet_id', $padlet_id)
                ->where('user_id', $user_id)
                ->first();
            return response()->json($userright1, 201);
        } catch (\Exception $e) {
            // Falls ein Fehler auftritt, wird eine Fehlermeldung mit dem HTTP-Statuscode 420 zurückgegeben,
            //und alle Datenbankänderungen werden rückgängig gemacht
            DB::rollBack();
            return response()->json("Updating userright failed: " . $e->getMessage(), 420);
        }
    }

    //Lösche Userright
    //sucht nach einem Userright-Eintrag in der Datenbank anhand der übergebenen Padlet-ID und Benutzer-ID
    public function delete(string $padlet_id, string $user_id): JsonResponse
    {
        $userright = Userright::where('padlet_id', $padlet_id)->where('user_id', $user_id)
            ->with(['user', 'padlet'])->first();
        //Wenn ein entsprechendes Userright gefunden wird, wird es aus der Datenbank gelöscht, und es wird eine
        //JSON-Antwort mit dem HTTP-Statuscode 200 zurückgegeben, die den erfolgreichen Löschvorgang bestätigt
        if ($userright != null) {
            $userright->delete();
            return response()->json('$userright (' . $padlet_id . " " . $user_id . ') successfully deleted', 200);
        } else
            //Wenn kein Userright gefunden wird, wird eine JSON-Antwort mit dem HTTP-Statuscode 422 zurückgegeben,
            //die angibt, dass der Löschvorgang nicht durchgeführt werden konnte, da das Userright nicht existiert.
            return response()->json('$userright could not be deleted - it does not exist', 422);
    }

    //ucht nach Userright-Einträgen in der Datenbank, die der übergebenen Padlet-ID entsprechen
    public function findByPadletId(string $padlet_id) : JsonResponse
    {
        //Dabei werden auch die zugehörigen Benutzer- und Padlet-Daten geladen.
        $userright = Userright::where('padlet_id', $padlet_id)
            ->with(['user', 'padlet'])->get();
        //Wenn entsprechende Userrights gefunden werden, werden sie als JSON-Antwort mit dem HTTP-Statuscode 200 zurückgegeben
        return $userright != null ? response()->json($userright, 200) : response()->json(null, 200);
    }

    //sucht nach Userright-Einträgen in der Datenbank, die der übergebenen Benutzer-ID entsprechen.
    public function findByUserId(string $user_id) : JsonResponse
    {
        //Dabei werden auch die zugehörigen Benutzer- und Padlet-Daten geladen.
        $userright = Userright::where('user_id', $user_id)
            ->with(['user', 'padlet'])->get();
        //Wenn entsprechende Userrights gefunden werden, werden sie als JSON-Antwort mit dem HTTP-Statuscode 200 zurückgegeben
        return $userright != null ? response()->json($userright, 200) : response()->json(null, 200);
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
