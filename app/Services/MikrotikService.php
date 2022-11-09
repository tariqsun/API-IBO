<?php



namespace App\Services;
use RouterOS;



class MikrotikService{


    public static $client;

    function __construct($client)
    {
        self::$client = $client;
    }

    public static function getClient($arg=[])
    {
        try {
           $client = new RouterOS\Client([
            'host'=>$arg['host'],
            'user'=>$arg['user'],
            'pass'=>$arg['pass']
           ]);

           return $client;
        } catch (\Exception $th) {
             return [
                'errors'=>true,
                'message'=>$th->getMessage()
             ];
        }
    }

    public static function addPpoeUser($params=[])
    {

        $client = self::$client;

        $query = (new RouterOS\Query('/ppp/secret/add'));

        foreach($params as $key => $val){
            $query->equal($key, $val);
        }

        $user = $client->query($query)->read();

        return $user['after']['ret'];

    }

    public static function setPpoeUser($params)
    {
        $client = self::$client;

        $query = (new RouterOS\Query('/ppp/secret/set'));

        foreach($params as $key => $val){
            $query->equal($key, $val);
        }

        $user = $client->query($query)->read();

        return $user;

    }

    public static function enableOrDisablePpoeUser($id, $status='enable')
    {
        $client = self::$client;

        $query = (new RouterOS\Query("/ppp/secret/{$status}"))
                    ->equal('.id', $id);

        $user = $client->query($query)->read();

        return $user;

    }


    public static function getPpoeUser($username)
    {

        $client = self::$client;

        $query = (new RouterOS\Query('/ppp/secret/print'))
                    ->where('name', $username);

        $user = $client->query($query)->read();

        if(count($user) > 0){
            return $user[0];
        }

        return false;
    }



    public static function isExistPpoeUser($username)
    {

        $client = self::$client;

        $query = (new RouterOS\Query('/ppp/secret/print'))
                    ->where('name', $username);

        $user = $client->query($query)->read();

        if(count($user) > 0){
            return true;
        }

        return false;
    }

    public static function removePpoeUser($id)
    {
        $client = self::$client;

        $query = (new RouterOS\Query('/ppp/secret/remove'))
                    ->equal('.id', $id);

        $remove = $client->query($query)->read();

        return $remove;

    }
}
