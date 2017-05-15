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
        /*$memcache = new \Memcache;
        $memcache->connect('loaclhost',11211);*/
        if(isset(static::$class[$name])){

        }else{
            static::$class[$name] = new $name;
        }
        return static::$class[$name];

       /* if($memcache->get($name) === false){
            $memcache->set($name, new $name);
            error_log('已存在类'.implode(',',array_keys(static::$class))."\r\n",3,'./log.log');
            error_log('新生成类'.$name."\r\n",3,'./log.log');
        }else{
            error_log('已生成类'.$name."\r\n",3,'./log.log');
        }
        return $memcache->get($name);*/
    }
}
