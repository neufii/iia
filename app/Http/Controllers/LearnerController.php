<?php

namespace App\Http\Controllers;

use App\Models\Learner;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;

class LearnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response::json([
            'status' => 'completed',
            'message' => 'Learners Retreived',
            'data' => Learner::get(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer','unique:learners,user_id'],
         ]);
      
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $learner = new Learner();
        $learner->user_id = $request['user_id'];
        $learner->save();

        return Response::json([
            'status' => 'completed',
            'message' => 'Learner Stored',
            'data' => $learner,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Response::json([
            'status' => 'completed',
            'message' => 'Learner Retreived',
            'data' => Learner::findOrFail($id),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer','unique:learners,user_id,'.$id],
         ]);
      
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $learner = Learner::findOrFail($id);
        $learner->user_id = $request['user_id'];
        $learner->save();

        return Response::json([
            'status' => 'completed',
            'message' => 'Learner Updated',
            'data' => $learner,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = Learner::destroy($id);

        return Response::json([
            'status' => 'completed',
            'message' => 'Learner Deleted',
        ], 200);
    }
}
