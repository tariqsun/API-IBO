<?php

namespace App\Http\Controllers;

use App\Models\Panel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function getAll()
    {
        $panels = Panel::where('user_id', Auth::id())->get();
        return $panels;
    }

    public function edit($id)
    {
        $panels = Panel::where('id', $id)->first();
        return $panels;
    }


    public function create(Request $request)
    {
        $request->validate([
           'name'=>['required', 'unique:panels']                 
        ]);

        Panel::create([
            'name'=>$request->name,
            'message'=>$request->message,
            'user_id'=>Auth::id()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
           'name'=>['required', 'unique:panels']                 
        ]);

        Panel::where('id', $id)->update([
            'name'=>$request->name,
            'message'=>$request->message,
        ]);
    }

    public function delete($id)
    {
        $panels = Panel::where('id', $id)->delete();
        return $panels;
    }
}
