<?php

namespace App\Middleware;
use App\Entity\User;

class Authentication {

    public function authenticate($request, $app)
    {

        $auth = $request->headers->get("Authorization");
        $apikey = substr($auth, strpos($auth, ' '));
        $apikey = trim($apikey);
        $user = new User();
        $check = $user->authenticate($apikey,$app);

        //Se não autenticou, retorne 401
        if(!$check){
            $app->abort(401,'Unauthorized - bad credentials');
        }

        else $request->attributes->set('userid',$check);

    }

}

?>