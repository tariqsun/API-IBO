<?php

namespace App\Http\Controllers;


use App\Services\MikrotikService;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    public function index()
    {
        try {
            $client =  MikrotikService::getClient();
            $add_user = [
                'name'=>'test-00232',
                'service'=>'pppoe',
                'password'=>'123123123',
            ];

            $service = new MikrotikService($client);
            // $service->enableOrDisablePpoeUser('*8', 'enable');
            // $service->getPpoeUser('test-001'); // get user
            // $service->isExistPpoeUser('test-001'); // is user exist
            // $service->setPpoeUser([]); update user
            // $service->addPpoeUser([]) add New User
            // $service->removePpoeUser(user_id) remove_id

            return $service->enableOrDisablePpoeUser('*8', 'enable');

        } catch (\Exception $th) {
            return response()->json([
                'errors'=>true,
                'message'=>$th->getMessage()
             ], 200);
        }
    }
}
