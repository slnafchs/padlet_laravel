<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitesController extends Controller
{
    public function index():JsonResponse{
        $invites = Invite::with(['user', 'padlet'])->get();
        return response()->json($invites, 200);
    }

    //Create new invite
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            $invites = Invite::create($request->all());
            DB::commit();
            // return a vaild http response
            return response()->json($invites, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("saving invite failed: " . $e->getMessage(), 420);
        }
    }

    //find invite by user ID
    public function findByUserId(string $user_id) : JsonResponse
    {
        $invites = Invite::where('user_id', $user_id)
            ->with(['user', 'padlet'])->get();
        return $invites != null ? response()->json($invites, 200) : response()->json(null, 200);
    }

    //find invite by padlet and user ID
    public function findIfExists(string $padlet_id, string $user_id) : JsonResponse
    {
        $invites = Invite::where('user_id', $user_id)->where('padlet_id', $padlet_id)
            ->with(['user', 'padlet'])->first();
        return $invites != null ? response()->json($invites, 200) : response()->json(null, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $invite = Invite::with(['user', 'padlet'])
                ->where('id', $id)
                ->first();

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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Updating invite failed: " . $e->getMessage(), 420);
        }
    }

    public function delete(string $id): JsonResponse
    {
        $invite = Invite::where('id', $id)
            ->with(['user', 'padlet'])->first();
        if ($invite != null) {
            $invite->delete();
            return response()->json('$invite (' . $id . ') successfully deleted', 200);
        } else
            return response()->json('$invite could not be deleted - it does not exist', 422);
    }


    private function parseRequest(Request $request): Request
    {
        // get date and convert it - its in ISO 8601, e.g. "2018-01-01T23:00:00.000Z"
        $date = new \DateTime($request->published);
        $request['published'] = $date;
        return $request;
    }
}
