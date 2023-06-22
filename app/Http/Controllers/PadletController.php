<?php

namespace App\Http\Controllers;

use App\Models\Padlet;
use App\Models\Entrie;
use App\Models\User;
use App\Models\Userright;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent;
use Illuminate\Support\Facades\DB;

class PadletController extends Controller
{
    //es werden alle Padlets (mit user, entries und userrights) aus der Datenbank abgerufen
    public function index(): JsonResponse
    {
        $padlets = Padlet::with(['user', 'entries', 'userrights'])->get(); //Padlet = Model
        return response()->json($padlets, 200);
    }

    //sucht entsprechendes Padlet in der Datenbank und gibt eine Ansicht zurück, in der das Padlet angezeigt wird
    public function show($padlet)
    {
        $padlet = Padlet::find($padlet);
        return view('padlets.show', compact('padlet'));
    }

    //gibt Padlet mit der entsprechenden ID zurück, wann es kein Padlet findet dann "null"
    public function findById(string $id): JsonResponse
    {
        $padlet = Padlet::where('id', $id)
            ->with(['user', 'entries', 'userrights'])->first();
        return $padlet != null ? response()->json($padlet, 200) : response()->json(null, 200);
    }

    //gibt öffentliche Padlets zurück, also wo is_public = TRUE ist
    public function getPublic(): JsonResponse
    {
        $padlets = Padlet::where('is_public', TRUE)
            ->with(['user', 'entries', 'userrights'])->get();
        return $padlets != null ? response()->json($padlets, 200) : response()->json(null, 200);
    }

    //ruft alle Padlets eines bestimmten Benutzers aus der Datenbank ab, indem sie nach Padlets sucht, bei denen
    //das Feld "user_id" mit der übergebenen User ID übereinstimmt
    public function getPadletsOfUser(string $user_id): JsonResponse
    {
        $padlets = Padlet::where('user_id', $user_id)
            ->with(['user', 'entries', 'userrights'])->get();
        return $padlets != null ? response()->json($padlets, 200) : response()->json(null, 200);
    }

    //überprüft, ob ein Padlet mit der angegebenen ID in der Datenbank vorhanden ist
    public function checkID(string $id): JsonResponse
    {
        $padlet = Padlet::where('id', $id)->first();
        return $padlet != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //sucht nach Padlets in der Datenbank, die entweder den Suchbegriff im Namen haben oder deren Benutzer (Ersteller)
    //den Suchbegriff im Vornamen oder Nachnamen haben
    public function findBySearchTerm(string $searchTerm): JsonResponse
    {
        $padlets = Padlet::with(['user', 'entries', 'userrights', 'entries.comments', 'entries.ratings'])
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orWhereHas('user', function ($query) use ($searchTerm) {
                $query->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');
            })->get();
        return response()->json($padlets, 200);
    }

    //speichert ein neues Padlet basierend auf den Daten, die in der übergebenen Anfrage (Request) enthalten sind
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        //sicherstellen, dass alle Datenbankoperationen entweder erfolgreich durchgeführt
        //oder bei einem Fehler rückgängig gemacht werden
        try {
            $padlet = Padlet::create($request->all());
            DB::commit();
            //wenn erfolgreich Padlet gespeichert wurde
            return response()->json($padlet, 201);
        } catch (\Exception $e) {
            //wenn Padlet nicht erfolgreich gespeichert wurde -> Fehler
            DB::rollBack();
            return response()->json("saving padlet failed: " . $e->getMessage(), 420);
        }
    }

    //aktualisiert ein vorhandenes Padlet in der Datenbank basierend auf den Daten, die in der übergebenen Anfrage
    //(Request) enthalten sind
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            //Padlet wird abgerufen
            $padlet = Padlet::with(['user', 'entries', 'userrights'])
                ->where('id', $id)->first();
            if ($padlet != null) {
                $request = $this->parseRequest($request);
                //padlet wird aktualisiert
                $padlet->update($request->all());

                //Userrights werden gelöscht
                $padlet->userrights()->delete();

                //Userrights werden geupdated
                if (isset($request['userrights']) && is_array($request['userrights'])) {
                    foreach ($request['userrights'] as $userrights) {
                        $userrights = Userright::firstOrNew(
                            ['padlet_id' => $userrights['padlet_id'],
                                'user_id' => $userrights['user_id'],
                                'read' => $userrights['read'],
                                'edit' => $userrights['edit'],
                                'delete' => $userrights['delete']]);
                        //neue Userrights werden gespeichert
                        $padlet->userrights()->save($userrights);
                    }
                }
                //Padlet wird gespeichert
                $padlet->save();
            }
            DB::commit();
            //wenn Padlet erfolgreich geupdated hat, wird es zurückgegeben
            $padlet1 = Padlet::with(['user', 'entries', 'userrights'])
                ->where('id', $id)->first();
            return response()->json($padlet1, 201);
        } catch (\Exception $e) {
            //wenn was schief gegangen ist
            DB::rollBack();
            return response()->json("updating padlet failed: " . $e->getMessage(), 420);
        }
    }

    //löscht ein Padlet aus der Datenbank, indem es nach dem Padlet mit der angegebenen ID sucht
    public function delete(string $id): JsonResponse
    {
        //wenn Padlet gefunden wird -> wird erfolgreich gelöscht
        $padlet = Padlet::where('id', $id)->first();
        if ($padlet != null) {
            $padlet->delete();
            return response()->json('padlet (' . $id . ') successfully deleted', 200);
        } else
            //wenn etwas nicht geklappt hat -> Fehler
            return response()->json('padlet could not be deleted - it does not exist', 422);
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
