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

    public function index():JsonResponse{
        $comment = Comment::with(['entrie','user'])->get();
        return response()->json($comment, 200);
    }

    public function findCommentsByID(string $id):JsonResponse{
        $comment = Comment::where('padlet_id', $id)
            ->with(['comment','user_id', 'entrie_id'])->get();
        return $comment != null ? response()->json($comment, 200) : response()->json(null, 200);
    }

    public function findByEntryID(string $entry_id):JsonResponse{
        $comment = Comment::where('entrie_id', $entry_id)
            ->with(['user', 'entrie'])->get();
        return $comment != null ? response()->json($comment, 200) : response()->json(null, 200);
    }

    //save/create Comment
    public function saveComment(Request $request, string $entrieID): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            if(isset($request['user_id']) &&isset($request['comment']));
            {
                $comment = Comment::create(
                    [
                        'user_id'=>$request['user_id'],
                        'comment'=>$request['comment'],
                        'entrie_id'=> $entrieID
                    ]
                );
            }
            DB::commit();
            // return a vaild http response
            return response()->json($comment, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("saving comment failed: " . $e->getMessage(), 420);
        }
    }

    private function parseRequest(Request $request): Request
    {
        // get date and convert it - its in ISO 8601, e.g. "2018-01-01T23:00:00.000Z"
        $date = new \DateTime($request->published);
        $request['published'] = $date;
        return $request;
    }
}
