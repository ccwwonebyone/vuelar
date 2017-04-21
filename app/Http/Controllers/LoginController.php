<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $user = $request->input('user');
        $pwd = $request->input('pwd');
        $userDb = new User;
        $res = $userDb->where(['user'=>$user,'pwd'=>$pwd])->count();
        if($res === 1){

        }
    }
}
