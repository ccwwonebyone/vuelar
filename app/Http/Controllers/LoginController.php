<?php

namespace App\Http\Controllers;
use App\Http\Controllers\SingelRowController as SingelRow;
use Illuminate\Http\Request;

class LoginController extends Controller
{
	private $models = ['user'=>'App\User'];
	public function index()
	{
		return view('index.login');
	}
    public function checkLogin(Request $request)
    {
        session_start();
        $user = $request->input('username');
        $pwd = $request->input('password');
        $userDb = SingelRow::getInstance($this->models['user']);
        $res = $userDb->where(['user'=>$user,'pwd'=>$pwd])->count('id');
        $data = [];
        if($res === 1){
            $data['status'] = true;
            $data['message'] = '登陆成功！';
            $_SESSION['username'] = $user;
        }else{
            $data['status'] = false;
            $data['message'] = '帐号或密码错误！';
        }
        $data['url'] = route('index');
        //$data['res'] = $res;
        return response()->json($data);
    }
}
