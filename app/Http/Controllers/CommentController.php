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

class CommentController extends Controller
{

    //gibt alle Kommentare aus der Datenbank abgerufen über das Eloquent-Modell "Comment" zurück
    public function index():JsonResponse{
        //Die Kommentare werden zusammen mit den dazugehörigen Einträgen und Benutzerinformationen abgerufen
        //und als JSON-Response mit dem HTTP-Statuscode 200 zurückgegeben.
        $comment = Comment::with(['entrie','user'])->get();
        return response()->json($comment, 200);
    }

    //sucht Kommentare basierend auf der übergebenen "padlet_id" in der Datenbank und gibt sie zusammen mit den
    //dazugehörigen Kommentaren, Benutzer-IDs und Eintrags-IDs zurück
    public function findCommentsByID(string $id):JsonResponse{
        $comment = Comment::where('padlet_id', $id)
            ->with(['comment','user_id', 'entrie_id'])->get();
        //wenn Kommentar gefunden wird, dann zurückgegeben, wenn leer dann = "null"
        return $comment != null ? response()->json($comment, 200) : response()->json(null, 200);
    }

    //sucht Kommentare basierend auf der übergebenen "entrie_id" in der Datenbank und gibt sie zusammen mit den
    //dazugehörigen Benutzern und Einträgen zurück.
    public function findByEntryID(string $entry_id):JsonResponse{
        $comment = Comment::where('entrie_id', $entry_id)
            ->with(['user', 'entrie'])->get();
        //wenn Kommentar gefunden wird, dann zurückgegeben, wenn leer dann = "null"
        return $comment != null ? response()->json($comment, 200) : response()->json(null, 200);
    }

    //erstellt einen Kommentar basierend auf den übergebenen Daten und der Eintrags-ID
    public function saveComment(Request $request, string $entrieID): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            //wenn Kommentar erfolgreich gespeichert wird ...
            if(isset($request['user_id']) &&isset($request['comment']));
            {
                //Kommentar mit user_id, comment und entrie_id wird erstellt
                $comment = Comment::create(
                    [
                        'user_id'=>$request['user_id'],
                        'comment'=>$request['comment'],
                        'entrie_id'=> $entrieID
                    ]
                );
            }
            DB::commit();
            // wird er als JSON-Response zurückgegeben
            return response()->json($comment, 201);
        } catch (\Exception $e) {
            // wenn nicht erfolgreich, Error Message
            DB::rollBack();
            return response()->json("saving comment failed: " . $e->getMessage(), 420);
        }
    }

    //sucht den Kommentar basierend auf der Kommentar-ID und aktualisiert die entsprechenden Felder mit den
    //übergebenen Daten aus der Anfrage
    public function update(Request $request, string $entrie_id, string $comment_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $comment = Comment::with(['entrie','user'])
                ->where('id', $comment_id)
                ->first();

            //wenn Kommentar vorhanden ist, wird er gespeichert
            if ($comment != null) {
                $request = $this->parseRequest($request);
                $comment->user_id =$request['user_id'];
                $comment->entrie_id =$request['entrie_id'];
                $comment->comment=$request['comment'];
                $comment->save();
            }

            DB::commit();
            $comment1 = Comment::with(['entrie','user'])
                ->where('id', $comment_id)
                ->first();
            //Kommentar wird mit einer JSON-Response zurückgegeben
            return response()->json($comment1, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            //wenn nicht erfolgreich, Error
            return response()->json("Updating comment failed: " . $e->getMessage(), 420);
        }
    }

    //sucht den Kommentar basierend auf der Kommentar-ID und löscht ihn, falls vorhanden
    public function delete(string $entrie_id, string $comment_id): JsonResponse
    {
        $comment = Comment::where('id', $comment_id)
            ->with(['entrie','user'])->first();
        if ($comment != null) {
            //wenn Kommentar erfolgreich gelöscht wird, JSON-Meldung mit "Kommentar erfolgreich gelöscht" zurückgegeben
            $comment->delete();
            return response()->json('$comment (' . $comment_id . ') successfully deleted', 200);
        } else
            //wenn nicht erfolgreich, Error Response
            return response()->json('$comment could not be deleted - it does not exist', 422);
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
