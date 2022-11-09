<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\CreateExpensRequest;
use App\Models\Expens;
use App\Models\ExpensCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensController extends Controller
{
    public function categories(){
        try {
            $user = Auth::user();

            $categories = ExpensCategory::where('user_id', $user->id)
                                          ->get();
            return $this->toJson($categories);
        } catch (\Exception $th) {
            return $this->toJson([]);
        }
    }

    public function getCategory($id){
        try {

            $category = ExpensCategory::where('id', $id)->first();

            if($category){
                return $this->toJson(['errors'=>false, 'category'=>$category]);
            }

           return $this->toJson(['errors'=>true, 'message'=>'No Record found with this id']);

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }

    public function createCategory(CreateCategoryRequest $request){
        try {
            $validated = $request->validated();
            if($validated){
                $user = Auth::user();

                $create = ExpensCategory::create([
                    'name'=>$request->name,
                    'status'=>$request->status,
                    'user_id'=>$user->id
                ]);

                if($create){
                    return $this->toJson(['errors'=>false, 'category'=>$create]);
                }

                return $this->toJson(['errors'=>true, 'message'=>'something went wrong please try again']);
            }

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }

    public function updateCategory($id, Request $request){
        try {
            $validated = $request->validate([
                'name'=>"required|unique:expens_categories,name,{$id}"
            ]);


           $update = ExpensCategory::where('id', $id)->update([
                'name'=>$request->name,
                'status'=>$request->status,
            ]);

            if($update){
               return $this->toJson(['errors'=>false, 'updated'=>$update]);
            }

            return $this->toJson(['errors'=>true, 'message'=>'something went wrong please try again']);

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }

    public function deleteCategory($id){
        try {

            $update = ExpensCategory::where('id', $id)->delete();

            if($update){
               return $this->toJson(['errors'=>false, 'category'=>$update]);
            }

            return $this->toJson(['errors'=>true, 'message'=>'something went wrong please try again']);

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }


    public function expens(){
        try {
            $user = Auth::user();

            $expens = Expens::select('expens.*', 'expens_categories.name')
                             ->join('expens_categories', 'expens.category_id', '=', 'expens_categories.id')
                             ->where('expens.user_id', $user->id)
                             ->get();
            return $this->toJson($expens);
        } catch (\Exception $th) {
            return $this->toJson([]);
        }
    }

    public function getExpens($id){
        try {

            $expens = Expens::where('id', $id)->first();

            if($expens){
                return $this->toJson(['errors'=>false, 'expens'=>$expens ]);
            }

           return $this->toJson(['errors'=>true, 'message'=>'No Record found with this id']);

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }

    public function createExpens(CreateExpensRequest $request){
        try {
            $validated = $request->validated();
            if($validated){
                $user = Auth::user();

                $create = Expens::create([
                    'category_id'=>$request->category,
                    'amount'=>$request->amount,
                    'expens_date'=>Carbon::parse($request->expens_date)->format('Y-m-d'),
                    'description'=>$request->description,
                    'user_id'=>$user->id
                ]);

                if($create){
                    return $this->toJson(['errors'=>false, 'expens'=>$create]);
                }

                return $this->toJson(['errors'=>true, 'message'=>'something went wrong please try again']);
            }

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }

    public function updateExpens($id, Request $request){
        try {
            $validated = $request->validate([
                'category'=>"required",
                'amount'=>['required','integer'],
                'expens_date'=>['required']
            ]);


           $update = Expens::where('id', $id)->update([
                'category_id'=>$request->category,
                'amount'=>$request->amount,
                'expens_date'=>Carbon::parse($request->expens_date)->format('Y-m-d'),
                'description'=>$request->description,
            ]);

            if($update){
               return $this->toJson(['errors'=>false, 'expens'=>$update]);
            }

            return $this->toJson(['errors'=>true, 'message'=>'something went wrong please try again']);

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }

    public function deleteExpens($id){
        try {

            $update = Expens::where('id', $id)->delete();

            if($update){
               return $this->toJson(['errors'=>false, 'expens'=>$update]);
            }

            return $this->toJson(['errors'=>true, 'message'=>'something went wrong please try again']);

        } catch (\Exception $th) {
            return $this->toJson(['errors'=>true, 'message'=>$th->getMessage()]);
        }
    }
}
