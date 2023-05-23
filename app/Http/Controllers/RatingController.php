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
    //find Rating by EntryID
    public function findByEntryID(string $entry_id):JsonResponse{
        $rating = Rating::where('entrie_id', $entry_id)
            ->with(['user', 'entrie'])->get();
        return $rating != null ? response()->json($rating, 200) : response()->json(null, 200);
    }

    //save Rating
    public function saveRating(Request $request, string $entrieID): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            if(isset($request['user_id']) &&isset($request['rating']));
            {
                $rating = Rating::create(
                    [
                        'user_id'=>$request['user_id'],
                        'rating'=>$request['rating'],
                        'entrie_id'=> $entrieID
                    ]
                );
            }
            DB::commit();
            // return a vaild http response
            return response()->json($rating, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("saving rating failed: " . $e->getMessage(), 420);
        }
    }

    public function update(Request $request, string $entrie_id, string $user_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $rating = Rating::with(['user', 'entrie'])
                ->where('entrie_id', $entrie_id)
                ->where('user_id', $user_id)
                ->first();

            if ($rating != null) {
                $request = $this->parseRequest($request);
                $rating->user_id =$request['user_id'];
                $rating->entrie_id =$request['entrie_id'];
                $rating->rating =$request['rating'];
                $rating->save();
            }

            DB::commit();

            $rating1 = Rating::with(['entrie', 'user'])
                ->where('entrie_id', $entrie_id)
                ->where('user_id', $user_id)
                ->first();

            return response()->json($rating1, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Updating rating failed: " . $e->getMessage(), 420);
        }
    }

    public function delete(string $entrie_id, string $user_id): JsonResponse
    {
        $rating = Rating::where('entrie_id', $entrie_id)->where('user_id', $user_id)
            ->with(['user', 'entrie'])->first();
        if ($rating != null) {
            $rating->delete();
            return response()->json('$rating (' . $entrie_id . " " . $user_id . ') successfully deleted', 200);
        } else
            return response()->json('$rating could not be deleted - it does not exist', 422);
    }

    private function parseRequest(Request $request): Request
    {
        // get date and convert it - its in ISO 8601, e.g. "2018-01-01T23:00:00.000Z"
        $date = new \DateTime($request->published);
        $request['published'] = $date;
        return $request;
    }
}
