<?php

namespace App\Http\Controllers;
use App\Models\Userright;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserrightsController extends Controller
{
    public function index():JsonResponse{
        $userright = Userright::with(['user', 'padlet'])->get();
        return response()->json($userright, 200);
    }

    //Create new Userright
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            $userright = Userright::create($request->all());
            DB::commit();
            // return a vaild http response
            return response()->json($userright, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("saving userright failed: " . $e->getMessage(), 420);
        }
    }

    //find userright by ID
    public function findById(string $padlet_id, string $user_id) : JsonResponse
    {
        $userright = Userright::where('padlet_id', $padlet_id)->where( 'user_id', $user_id)
            ->with(['user', 'padlet'])->first();
        return $userright != null ? response()->json($userright, 200) : response()->json(null, 200);
    }

    //Update Userright
    public function update(Request $request, string $padlet_id, string $user_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userright = Userright::with(['padlet', 'user'])
                ->where('padlet_id', $padlet_id)
                ->where('user_id', $user_id)
                ->first();

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

            DB::commit();

            $userright1 = Userright::with(['padlet', 'user'])
                ->where('padlet_id', $padlet_id)
                ->where('user_id', $user_id)
                ->first();

            return response()->json($userright1, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Updating userright failed: " . $e->getMessage(), 420);
        }
    }

    public function delete(string $padlet_id, string $user_id): JsonResponse
    {
        $userright = Userright::where('padlet_id', $padlet_id)->where('user_id', $user_id)
            ->with(['user', 'padlet'])->first();
        if ($userright != null) {
            $userright->delete();
            return response()->json('$userright (' . $padlet_id . " " . $user_id . ') successfully deleted', 200);
        } else
            return response()->json('$userright could not be deleted - it does not exist', 422);
    }


    private function parseRequest(Request $request): Request
    {
        // get date and convert it - its in ISO 8601, e.g. "2018-01-01T23:00:00.000Z"
        $date = new \DateTime($request->published);
        $request['published'] = $date;
        return $request;
    }
}
