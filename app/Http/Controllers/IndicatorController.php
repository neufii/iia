<?php

namespace App\Http\Controllers;

use App\Models\Indicator;

use Illuminate\Http\Request;
use Response;

class IndicatorController extends Controller
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
            'message' => 'Indicators Retreived',
            'data' => Indicator::get(),
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
        $validatedRequest = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $indicator = new Indicator();
        $indicator->name = $validatedRequest['name'];
        if(isset($validatedRequest['description'])){
            $indicator->description = $validatedRequest['description'];
        }

        $indicator->save();

        return Response::json([
            'status' => 'completed',
            'message' => 'Indicator Stored',
            'data' => $indicator,
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
            'message' => 'Indicator Retreived',
            'data' => Indicator::findOrFail($id),
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
        $validatedRequest = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $indicator = Indicator::findOrFail($id);
        $indicator->name = $validatedRequest['name'];
        if($validatedRequest['description']){
            $indicator->description = $validatedRequest['description'];
        }

        $indicator->save();

        return Response::json([
            'status' => 'completed',
            'message' => 'Indicator Updated',
            'data' => $indicator,
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
        $deleted = Indicator::destroy($id);

        return Response::json([
            'status' => 'completed',
            'message' => 'Indicator Deleted',
            'data' => $deleted,
        ], 200);
    }
}
