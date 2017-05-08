<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SingelRowController as SingelRow;
use Illuminate\Http\Request;

class InstallController extends Controller
{
    private $controllers = ['DataBase'=>'App\Http\Controllers\DataBaseController'];

    public function index()
    {
        return view('install.install');
    }
    /**
     * 监测环境
     * @return json   各个监测项
     */
    public function checkEnv()
    {
        //安装目录，环境要求文件
        $reqiures = $this->getInstallFile('envRequire');

        foreach ($reqiures as &$check) {
            if($check['type'] == 'function'){
                if(function_exists($check['require'])){
                    $check['result'] = true;
                }
            }
            if($check['type'] == 'version'){
                if(version_compare(PHP_VERSION,$check['require'],'>=')){
                    $check['result'] = true;
                }
            }
        }
        return response()->json($reqiures);
    }
    /**
     * 创建数据库
     * @return
     */
    function createDatabase($connection,$name)
    {
        $connection->statement('CREATE DATABASE IF NOT EXISTS '.$name);
    }
    /**
     * 执行sql文件
     * @param  object $connection 连接
     * @param  string $sqlFile  sql文件名
     * @return boolean true/false
     */
    function createTable($sqlFile,$config)
    {
        foreach ($config as $key => $value) {
            config(['database.connections.mysql.'.$key => $value]);
        }
        $sqlString = file_get_contents(base_path().'/install'.'/'.$sqlFile);
        $sqlArr = explode(';', $sqlString);
        foreach ($sqlArr as $sql) {
            $sql = trim($sql);
            if($sql == '') continue;
            $sql = $this->parseComment($sql);
            DB::statement($sql);
        }
        return true;
    }
    /**
     * 获取安装相关文件
     * @param  string $name 安装文件文件名
     * @return array       安装文件的相关信息
     */
    function getInstallFile($name)
    {
        return require(base_path().'/install'.'/'.$name.'.php');
    }
    /**
     * 安装数据库表
     * @param  Request $request 请求信息
     * @return json
     */
    public function start(Request $request)
    {
        $database = SingelRow::getInstance($this->controllers['DataBase']);
        //更改数据库
        try {
            $config['username'] = $request->input('username');
            $config['password'] = $request->input('password');
            $config['database'] = 'mysql';            //初始连接到mysql数据库，成功连接到数据库
            $databaseName = $request->input('database');
            //创建数据库
            $connect = $database->configDB($config);
            $this->createDatabase($connect,$databaseName);
            //创建数据表
            $config['database'] = $databaseName;
            $databases = $this->getInstallFile('database');
            $this->createTable($databases[$databaseName],$config);

            $result['status'] = true;
            $result['message'] = $databaseName.'数据库初始化成功';
        } catch (Exception $e) {
            $result['status'] = false;
            $result['message'] = $databaseName.'数据库初始化失败';
        }
        return response()->json($result);
    }
    //分析注释
    public function parseComment($string)
    {
        $string = preg_replace("/((\/\*).*(\*\/))|((--).*(--))/s",'',$string);
        return $string;
    }
    /**
     * 获取需要安装的数据库
     * @return json
     */
    public function getInstallDatabases()
    {
        $databases = array_keys($this->getInstallFile('database'));
        $bases = [];
        foreach ($databases as $value) {
            $bases[] = ['name'=>$value,'status'=>'wait'];
        }
        return response()->json($bases);
    }
}
