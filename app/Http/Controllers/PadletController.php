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
    public function index(): JsonResponse
    {
        $padlets = Padlet::with(['user', 'entries', 'userrights'])->get();
        return response()->json($padlets, 200);
    }

    public function show($padlet)
    {
        $padlet = Padlet::find($padlet);
        return view('padlets.show', compact('padlet'));
    }

    //Finde Padlet mit ID
    public function findById(string $id): JsonResponse
    {
        $padlet = Padlet::where('id', $id)
            ->with(['user', 'entries', 'userrights'])->first();
        return $padlet != null ? response()->json($padlet, 200) : response()->json(null, 200);
    }

    //Finde öffentliche Padlets
    public function getPublic(): JsonResponse
    {
        $padlets = Padlet::where('is_public', TRUE)
            ->with(['user', 'entries', 'userrights'])->get();
        return $padlets != null ? response()->json($padlets, 200) : response()->json(null, 200);
    }

    //Finde Padlets mittels User ID
    public function getPadletsOfUser(string $user_id): JsonResponse
    {
        $padlets = Padlet::where('user_id', $user_id)
            ->with(['user', 'entries', 'userrights'])->get();
        return $padlets != null ? response()->json($padlets, 200) : response()->json(null, 200);
    }

    //Überprüfe ID
    public function checkID(string $id): JsonResponse
    {
        $padlet = Padlet::where('id', $id)->first();
        return $padlet != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //Finde Padlet durch Suchterm
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

    //Speichere neues Padlet
    public function save(Request $request): JsonResponse
    {
        $request = $this->parseRequest($request);
        DB::beginTransaction();

        try {
            $padlet = Padlet::create($request->all());
            DB::commit();
            // return a vaild http response
            return response()->json($padlet, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("saving padlet failed: " . $e->getMessage(), 420);
        }
    }

    //Update Padlet
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $padlet = Padlet::with(['user', 'entries', 'userrights'])
                ->where('id', $id)->first();
            if ($padlet != null) {
                $request = $this->parseRequest($request);
                $padlet->update($request->all());

                //delete all userrights
                $padlet->userrights()->delete();

                //Update Userrights
                if (isset($request['userrights']) && is_array($request['userrights'])) {
                    foreach ($request['userrights'] as $userrights) {

                        $userrights = Userright::firstOrNew(
                            ['padlet_id' => $userrights['padlet_id'],
                                'user_id' => $userrights['user_id'],
                                'read' => $userrights['read'],
                                'edit' => $userrights['edit'],
                                'delete' => $userrights['delete']]);
                        $padlet->userrights()->save($userrights);
                    }
                }
                $padlet->save();
            }
            DB::commit();
            $padlet1 = Padlet::with(['user', 'entries', 'userrights'])
                ->where('id', $id)->first(); // return a vaild http response
            return response()->json($padlet1, 201);
        } catch (\Exception $e) {
            // rollback all queries
            DB::rollBack();
            return response()->json("updating padlet failed: " . $e->getMessage(), 420);
        }
    }

    //Lösche Padlet
    public function delete(string $id): JsonResponse
    {
        $padlet = Padlet::where('id', $id)->first();
        if ($padlet != null) {
            $padlet->delete();
            return response()->json('padlet (' . $id . ') successfully deleted', 200);
        } else
            return response()->json('padlet could not be deleted - it does not exist', 422);
    }

    private function parseRequest(Request $request): Request
    {
        // get date and convert it - its in ISO 8601, e.g. "2018-01-01T23:00:00.000Z"
        $date = new \DateTime($request->published);
        $request['published'] = $date;
        return $request;
    }


}
