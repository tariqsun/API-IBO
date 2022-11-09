<?php

namespace App\Http\Controllers;

use App\Models\MikrotikNas;
use App\Rules\NasUniqueRule;
use App\Rules\UniqueIpRule;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class NasController extends Controller
{
    function get(){
        $user_id = auth()->user()->id;
        $nas = MikrotikNas::where('user_id', $user_id)->get();
        $response = [];

        foreach($nas as $key => $value){

            $client = MikrotikService::getClient([
                'user'=>$value['username'],
                'pass'=>$value['password'],
                'host'=>$value['ip']
            ]);

            if(is_array($client) && array_key_exists('errors', $client)){
                $response[] = [
                    'id'=>$value['id'],
                    'name'=>$value['name'],
                    'user'=>$value['username'],
                    'pass'=>$value['password'],
                    'host'=>$value['ip'],
                    'status'=>'unabled to connect',
                    'message'=>$client['message']
                ];
            }else{
                $response[] = [
                    'id'=>$value['id'],
                    'name'=>$value['name'],
                    'user'=>$value['username'],
                    'pass'=>$value['password'],
                    'host'=>$value['ip'],
                    'status'=>'connected',
                    'message'=>''
                ];
            }

        }

        return $response;
    }

    public function isNas($arg)
    {
        $client = MikrotikService::getClient($arg);

        if(is_array($client) && array_key_exists('errors', $client)){
            return $client;
        }

        return [
            'errors'=>false,
            'client'=>$client
        ];
    }


    function getById($id){
        $nas = MikrotikNas::where('id', $id)->first();
        return $nas;
    }

    function store(Request $request){

        $request->validate([
            'name'=>['required', 'min:5',new NasUniqueRule()],
            'ip'=>['required', 'unique:mikrotik_nas', 'ip'],
            'username'=>['required'],
            'password'=>['required']
        ]);

        $check_is_nas = $this->isNas([
            'host'=>$request->ip,
            'user'=>$request->username,
            'pass'=>$request->password
        ]);

        if($check_is_nas['errors'] != true){
            $nas = MikrotikNas::create([
                'name'=>$request->name,
                'ip'=>$request->ip,
                'username'=>$request->username,
                'password'=>$request->password,
                'user_id'=>auth()->user()->id
            ]);
        }else{
          return $check_is_nas;
        }

        return $check_is_nas;


        // return $nas;
    }

    function update(Request $request, $id){

        $request->validate([
            'name'=>['required', 'min:4', new NasUniqueRule($id)],
            'ip'=>['required', 'ip', new UniqueIpRule($id)],
            'username'=>['required'],
            'password'=>['required']
        ]);

         $check_is_nas = $this->isNas([
            'host'=>$request->ip,
            'user'=>$request->username,
            'pass'=>$request->password
        ]);

        if($check_is_nas['errors'] != true){
             $nas = MikrotikNas::where('id', $id)
                ->update([
                    'name'=>$request->name,
                    'ip'=>$request->ip,
                    'username'=>$request->username,
                    'password'=>$request->password,
            ]);

           return $nas;
        }else{
          return $check_is_nas;
        }

        return $check_is_nas;



    }

    function delete($id){
        $nas = MikrotikNas::where('id', $id)->delete();
        return $nas;
    }
}
