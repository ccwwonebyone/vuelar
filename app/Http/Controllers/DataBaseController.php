<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Database_config as DBC;
use App\Column;
use App\Table;

class DataBaseController extends Controller
{
    public function showTabs()
    {
    	$config = [
    		'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'sqlar',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'=>''
    	];
    	$manp = $this->configDB($config);
    	$tabs = $this->getTables($manp,$config['prefix']);
    	$tabToColumn = [];
    	foreach ($tabs as $tab) {
    		$columns = $this->getColumns($manp,$tab);
    		$tabToColumn[$tab] = $columns;
    	}
    	echo '<pre>';
    	print_r($tabToColumn);
    	echo '</pre>';
    }
    /**
     * 获取对应数据库表的字段详细信息
     * @param  Connection $laravelDbCon laravel数据库连接对象
     * @param  string $table       数据表
     * @return array               数据表字段
     */
    public function getColumns($laravelDbCon,$table)
    {
   		$columns = $laravelDbCon->select('show full columns from '.$table);
        foreach ($columns as &$column) {
            $column = get_object_vars($column);
        }
   		return $columns;
    }
    /**
     * 获取数据表信息
     * @param Connection $laravelDbCon laravel数据库连接对象
     * @return array 数据表
     */
    public function getTables($laravelDbCon,$prefix){
    	$tables = $laravelDbCon->select('show tables');
    	$tabs = [];
    	foreach ($tables as $table) {
    		$tabs[] = str_replace($prefix,'',array_values(get_object_vars($table))[0]);
    	}
    	return $tabs;
    }
    /**
     * 通过动态的修改数据库的配置文件进行，动态配置数据库
     * @param  array $config 数据库的配置信息与laravel配置信息一致
     * @return DB连接对象
     */
    public function configDB($config)
    {
    	foreach ($config as $key => $value) {
    		config(['database.connections.change.'.$key => $value]);
    	}
    	$newdb = DB::connection('change');
    	return $newdb;
    }
    /**
     * 根据数据库id
     * @param [type] $id [description]
     */
    public function DBInId($id)
    {
        $dbc = new DBC;
        $config = $dbc->where(['id'=>$id])
                      ->select('driver','database','host','username','password','prefix','charset','port','collation')
                      ->first()->toArray();
        $newdb = $this->configDB($config);
        return $newdb;
    }
    /**
     * 删除数据表
     * @param  int $tabId 表ID
     * @return boolean  是否成功
     */
    public function delTable($tabId)
    {
        $ColumnDb = new Column;
        $ColumnDb->where(['table_id'=>$tabId])->delete();
        $tabDb = new Table;
        $res = $tabDb->where(['id'=>$tabId])->delete();
        return $res;
    }
    /**
     * 删除数据库 删除啊数据库所有相关信息
     * @return boolean  是否成功
     */
    public function delDatabase($dbId)
    {
        $tabDb = new Table;
        $table_ids = $tabDb->where(['db_id'=>$dbId])->pluck('id');
        foreach ($table_ids as $table_id) {
            $this->delTable($table_id);
        }
        $tabDb->where(['db_id'=>$dbId])->delete();
        $dbc = new DBC;
        $res = $dbc->where(['id'=>$dbId])->delete();
        return $res;
    }
    public function insertTable($tab,$laravelDbCon,$dbId,$prefix,$isIconvToUtf8 = false)
    {
        $tableDb = new Table;
        $tabId = $tableDb->insertGetId(['name'=>$tab,'db_id'=>$dbId]);
        $columns = $this->getColumns($laravelDbCon,$prefix.$tab);
        foreach ($columns as &$column) {
            $column['table_id'] = $tabId;
            if($isIconvToUtf8) $column['Comment'] = iconv('gbk','utf-8',$column['Comment']);
            unset($column['Collation'],$column['Extra'],$column['Privileges']);
        }
        $columnDb = new Column;
        $res = $columnDb->insert($columns);
        return $res;
    }
}
