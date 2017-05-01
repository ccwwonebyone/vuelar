<?php
namespace App\Http\Controllers;

class SingelRowController extends Controller
{
    private static $class = [];   //类数组
    //私有构造函数，防止外界实例化对象
    private function __construct(){}
    //私有克隆函数，防止外界克隆对象
    private function __clone() {}
    //静态方法，单例统一访问入口
    public static function getInstance($name)
    {
        $class = static::$class;
        if(!in_array($name,array_keys($class))){
            $class[$name] = new $name;
        }
        return $class[$name];
    }
}
