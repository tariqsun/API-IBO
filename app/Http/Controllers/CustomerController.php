<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\MikrotikNas;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\Plan;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::user()->id;
        $customers = Customers::where('customers.user_id', $user_id)
                        ->where('payments.status', 0)
                        ->join('plans', 'customers.plan_id', '=', 'plans.id')
                        ->join('payments', 'customers.id', 'payments.customer_id')
                        ->select('customers.*', 'plans.name as plan_name', 'plans.price as amount', 'payments.balance as due_amount', 'payments.due_date as due_date')
                        ->orderBy('id', 'DESC')
                        ->get();

       return  $customers;
    }

    public function list()
    {
        $customers = Customers::select('id', 'name')->where('user_id', auth()->user()->id)
                                ->where('status', 1)
                                ->get();

        return $customers;
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
            'name'=>'required',
            'plan'=>'required',
            'service_type'=>['required'],
            'panel'=>['required_if:service_type,1'],
            'nas'=>['required_if:service_type,0'],
            'customer_id'=>'required|unique:customers',
            'status'=>'required'
        ]);

        $user = Auth::user();

        try {
            if($request->service_type == '0'){

                $nas = MikrotikNas::where('id', $request->nas)->first();
                $nasClient = MikrotikService::getClient([
                    'host'=> $nas['ip'],
                    'pass'=> $nas['password'],
                    'user'=> $nas['username'],
                ]);

                if(is_array($nasClient) && array_key_exists('errors', $nasClient)){
                    return $nasClient;
                }

                $nasService = new MikrotikService($nasClient);


                if(!$nasService->isExistPpoeUser($request->customer_id)){
                    $nas_user = $nasService->addPpoeUser([
                        'name'=>$request->customer_id,
                        'service'=>'pppoe',
                        'password'=>$request->password,
                        'disabled'=>$request->status=="0"?'yes':'no'
                    ]);
                }else{
                    $nas_user = $nasService->getPpoeUser($request->customer_id)['.id'];
                }


                $carbon = new Carbon($request->start_date);
                $customer = Customers::create([
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'discount'=>$request->discount,
                    'phone_number'=>$request->phone_number,
                    'customer_id'=>$request->customer_id,
                    'service_type'=>$request->service_type??1,
                    'service_id'=>$request->nas,
                    'address'=>$request->address,
                    'status'=>$request->status??true,
                    'start_date'=>$carbon->format('Y-m-d H:i:s'),
                    'password'=>$request->password,
                    'plan_id'=>$request->plan,
                    'user_id'=>$user->id
                ]);

                $plan = Plan::where('id', $request->plan)->first();
                Payment::create([
                    'customer_id'=>$customer->id,
                    'balance'=>$plan->price,
                    'start_date'=>$carbon->format('Y-m-d H:i:s'),
                    'due_date'=>$carbon->addMonth()->format('Y-m-d H:i:s'),
                    'user_id'=>$user->id,
                    'status'=>0
                ]);

                if($customer){
                    return $customer;
                }

            }else{

                $carbon = new Carbon($request->start_date);
                $customer = Customers::create([
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'discount'=>$request->discount,
                    'phone_number'=>$request->phone_number,
                    'customer_id'=>$request->customer_id,
                    'service_type'=>$request->service_type??1,
                    'service_id'=>$request->panel,
                    'address'=>$request->address,
                    'status'=>$request->status??true,
                    'start_date'=>$carbon->format('Y-m-d H:i:s'),
                    'password'=>$request->password,
                    'plan_id'=>$request->plan,
                    'user_id'=>$user->id
                ]);

                $plan = Plan::where('id', $request->plan)->first();
                Payment::create([
                    'customer_id'=>$customer->id,
                    'balance'=>$plan->price,
                    'start_date'=>$carbon->format('Y-m-d H:i:s'),
                    'due_date'=>$carbon->addMonth()->format('Y-m-d H:i:s'),
                    'user_id'=>$user->id,
                    'status'=>0
                ]);

                if($customer){
                    return $customer;
                }
            }
        } catch (\Exception $th) {
            return [
                'errors'=>true,
                'message'=>$th->getMessage()
            ];
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         return Customers::where('id', $id)->first();
    }


    public function refresh($id)
    {
         return Customers::where('id', $id)->update([
            'last_payment_date'=>now()->format('Y-m-t')
         ]);
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
            'plan'=>'required',
            'service_type'=>['required'],
            'panel'=>['required_if:service_type,1'],
            'nas'=>['required_if:service_type,0'],
            'customer_id'=>'required',
            'status'=>'required'
        ]);

        $user = Auth::user();

        $carbon = new Carbon($request->start_date);
        $plan = Plan::where('id', $request->plan)->first();

        $customer = Customers::where('id', $id)->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'discount'=>$request->discount,
            'phone_number'=>$request->phone_number,
            'customer_id'=>$request->customer_id,
            'address'=>$request->address,
            'status'=>$request->status??true,
            'start_date'=>$carbon->format('Y-m-d H:i:s'),
            'password'=>$request->password,
            'plan_id'=>$request->plan,
            'user_id'=>$user->id
        ]);


        if($customer){
            return $customer;
        }

        return [];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       try {

            $customer =  Customers::where('id', $id)->first();
            if($customer->service_type == '0'){
                $nas = MikrotikNas::where('id', $customer->service_id)->first();
                $nasClient = MikrotikService::getClient([
                    'host'=>$nas->ip,
                    'user'=>$nas->username,
                    'pass'=>$nas->password,
                ]);

                if(is_array($nasClient) && array_key_exists('errors', $nasClient)){
                    return $nasClient;
                }

                $nasService =  new MikrotikService($nasClient);
                $nas_user = $nasService->getPpoeUser($customer->customer_id);
                $nasService->removePpoeUser($nas_user['.id']);
                $delete_payments = Payment::where('customer_id', $id)->delete();
                if($delete_payments){
                    return Customers::where('id', $id)->delete();
                }
            }else{
                $delete_payments = Payment::where('customer_id', $id)->delete();
                if($delete_payments){
                    return Customers::where('id', $id)->delete();
                }
            }


       } catch (\Exception $th) {
         return [
            'errors'=>true,
            'message'=>$th->getMessage()
         ];
       }
    }


    public function count()
    {
        $user = Auth::user();
        $customers = Customers::where('user_id', $user->id)->count();
        return ['count'=>$customers];
    }

    public function import()
    {
        $get_file_path = storage_path('/Customers.csv');
        if(file_exists($get_file_path)){
            $customer =  [];
            $file = @fopen($get_file_path, 'r');
            while(($getData = fgetcsv($file, 10000, ",")) !== FALSE){
                $plan = Plan::where('name', $getData[4])->first();

                Customers::create([
                    'customer_id'=>$getData[0],
                    'name'=>$getData[1],
                    'phone_number'=>$getData[2],
                    'plan_id'=>isset($plan)?$plan->id:5,
                    'status'=>true,
                    'user_id'=>12
                ]);
            }


            @fclose($file);

        }
    }
}
