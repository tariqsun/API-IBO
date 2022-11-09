<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::user()->id;
        $plans = Plan::where('user_id', $user_id)
                        ->where('status', 1)
                        ->get();

        return response()->json($plans, 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>['required', 'unique:plans'],
            'price'=>['required'],
            'status'=>'required'
        ]);

        $user = Auth::user();

        $plan = Plan::create([
            'name'=>$request->name,
            'price'=>$request->price,
            'status'=>$request->status,
            'user_id'=>$user->id
        ]);

        if($plan){
            return $plan;
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return Plan::where('id', $id)->first();
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
        $request->validate([
            'name'=>'required',
            'status'=>'required'
        ]);


        $plan = Plan::where('id', $id)->update([
            'name'=>$request->name,
            'price'=>$request->price,
            'status'=>$request->status,
        ]);

        if($plan){
            return $plan;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Plan::where('id', $id)->delete();
    }
}
