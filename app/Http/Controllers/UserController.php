<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    //Finde User mittels ID
    public function findById(string $id): JsonResponse
    {
        $user = User::where('id', $id)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        return $user != null ? response()->json($user, 200) : response()->json(null, 200);
    }

    //Finde User mittels Email
    public function findByEmail(string $mail): JsonResponse
    {
        $user = User::where('email', $mail)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        return $user != null ? response()->json($user, 200) : response()->json(null, 200);
    }

    //Speichere neuen User
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            $user = User::create($request->all());
            DB::commit();
            // return a vaild http response
            return response()->json($user, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("saving users failed: " . $e->getMessage(), 420);
        }
    }

    //Update User
    public function update(Request $request, string $user_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])
                ->where('id', $user_id)
                ->first();

            if ($user != null) {
                $request = $this->parseRequest($request);
                $user->firstName =$request['firstName'];
                $user->lastName =$request['lastName'];
                $user->email = $request['email'];
                $user->password = $request['password'];
                $user->image =$request['image'];
                $user->save();
            }

            DB::commit();

            $user1 = User::with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])
                ->where('id', $user_id)
                ->first();

            return response()->json($user1, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json("Updating user failed: " . $e->getMessage(), 420);
        }
    }

    //Lösche User
    public function delete(string $user_id): JsonResponse
    {
        $user = User::where('id', $user_id)
            ->with(['entries', 'userrights', 'padlets', 'ratings', 'comments'])->first();
        if ($user != null) {
            $user->delete();
            return response()->json('$user (' .  $user_id . ') successfully deleted', 200);
        } else
            return response()->json('$user could not be deleted - it does not exist', 422);
    }


    private function parseRequest(Request $request): Request
    {
        $date = new \DateTime($request->published);
        $request['published'] = $date;
        return $request;
    }
}
