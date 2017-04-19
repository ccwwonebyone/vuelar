<?php

namespace App\Http\Controllers;

use App\Http\Controllers\DataBaseController as DataBase;
use App\Database_config as DataBase_c;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Column;
use App\Table;

class IndexController extends Controller
{
    public function index($id = '')
    {
        if($id == ''){
            $DataBase_c = new DataBase_c;
            $databases = $DataBase_c->get()->toArray();
            if(!empty($databases)) $id = $databases[0]['id'];
        }
        return view('index.index',['id'=>$id]);
    }

    public function insertDbInfo(Request $request)
    {
        $data = $request->all();
        unset($data['_token']);
        foreach ($data as $key=>$value) {
            if($key == 'prefix') continue;
            if($value == '') unset($data[$key]);
        }
        $isIconvToUtf8 = false;     //是否转换编码
        switch ($data['charset']) {
            case 'utf8':
                $data['collation'] = 'utf8_general_ci';
                break;
            case 'utf8mb4':
                $data['collation'] = 'utf8mb4_general_ci';
                break;
            case 'latin1':
                $data['collation'] = 'latin1_swedish_ci';
                $isIconvToUtf8 = true;
                break;
            case 'gbk':
                $data['collation'] = 'gbk_general_ci';
                $isIconvToUtf8 = true;
                break;
        }
        $DataBase_c = new DataBase_c;
        $insertId = $DataBase_c->insertInfo($data);

        $newdb = new DataBase;
        $manp = $newdb->configDB($data);
        $tabs = $newdb->getTables($manp,$data['prefix']);
        $tableDb = new Table;
        $columnDb = new Column;
        $db_id = $insertId;
        foreach ($tabs as $value) {
            $res = $newdb->insertTable($value,$manp,$db_id,$data['prefix'],$isIconvToUtf8);
        }
        return redirect()->back();
    }
    /**
     * 获取页面数据
     * @return json
     */
    public function getInfo(Request $request)
    {
        $db_id = $request->input('db_id');
        $tableDb = new Table;
        $tables = $tableDb->where(['db_id'=>$db_id])->get()->toArray();
        $DataBase_c = new DataBase_c;
        $databases = $DataBase_c->get()->toArray();
        $columnDb = new Column;
        foreach ($databases as &$value) {
            if($value['id'] == $db_id) $value['show']=true;
        }
        foreach ($tables as &$table) {
            $table['data'] = $columnDb->where(['table_id'=>$table['id']])->get()->toArray();
        }
        $data['tables'] = $tables;
        $data['databases'] = $databases;
        return response()->json($data);
    }
    /**
     * 更新数据 接收Comment/indtroduce字段
     * @return json
     */
    public function update(Request $request)
    {
        $info  = $request->all();
        $table = $info['table'];
        $field = $info['field'];
        $id    = $info['id'];
        $value = $info['info'];
        $data[$field] = $value;
        switch ($table) {
            case 'database':
                $db = new DataBase_c;
                break;
            case 'table':
                $db = new Table;
                break;
            case 'column':
                $db = new Column;
                break;
        }
        $where['id'] = $id;
        $res = $db->where($where)->update($data);
        $ret['status'] = $res ? true : false;
        return response()->json($ret);
    }
    /**
     * 更新数据表
     * @param  Request $request 请求
     * @return json
     */
    public function updateTable(Request $request)
    {
        $tableId = $request->input('id');
        $tableDb = new Table;
        $dbInfo = $tableDb->where(['id'=>$tableId])
                          ->select('db_id','name')
                          ->first()->toArray();
        $dbc = new DataBase_c;
        $dataInfo = $dbc->where(['id'=>$dbInfo['db_id']])->select('prefix','charset')->first()->toArray();
        $prefix = $dataInfo['prefix'];
        $charset = $dataInfo['charset'];

        $db = new DataBase;
        $newdb = $db->DBInId($dbInfo['db_id']);

        $columnDb = new Column;
        $old_columns = $columnDb->where(['table_id'=>$tableId])->get()->toArray();
        $columns = $db->getColumns($newdb,$prefix.$dbInfo['name']);

        $oldField = array_column($old_columns,'Field');
        $field = array_column($columns,'Field');

        $delColumns = array_diff($oldField,$field);
        $insertColumns = array_diff($field,$oldField);
        $updateColemns = array_intersect($field,$oldField);

        //删除消失字段
        foreach ($delColumns as $column) {
            $where['table_id'] = $tableId;
            $where['Field']    = $column;
            $columnDb->where($where)->delete();
        }
        unset($column);
        $isIconvToUtf8 = false;     //是否转换编码
        if($charset == 'latin1' || $charset == 'gbk') $isIconvToUtf8 = true;
        foreach ($columns as $column) {
            //更新字段 不更新Comment
            if(in_array($column['Field'],$updateColemns)){
                $where['table_id'] = $tableId;
                $where['Field']    = $column['Field'];
                unset($column['Collation'],$column['Extra'],$column['Privileges'],$column['Comment']);
                $columnDb->where($where)->update($column);
            }
            //新增字段
            if(in_array($column['Field'],$insertColumns)){
                $column['table_id'] =  $tableId;
                if($isIconvToUtf8) $column['Comment'] = iconv('gbk','utf-8',$column['Comment']);
                unset($column['Collation'],$column['Extra'],$column['Privileges']);
                $columnDb->insert($column);
            }
        }
        $ret['status'] = true;
        return response()->json($ret);
    }
    /**
     * 更新数据库表  用户删除或者新增数据表
     * 如需更改数据表的话 请使用updateTable
     * @return
     */
    public function updateDatabase(Request $request)
    {
        $id = $request->input('id');
        $db = new DataBase;

        $dbcDb = new DataBase_c;
        $prefix = $dbcDb->where(['id'=>$id])->value('prefix');
        $newdb = $db->DBInId($id);
        $tabs = $db->getTables($newdb,$prefix);
        $tableDb = new Table;
        $oldTabs = $tableDb->where(['db_id'=>$id])->pluck('name','id')->toArray();
        $delTabs = array_diff($oldTabs,$tabs);
        $insertTabs = array_diff($tabs,$oldTabs);
        $valToKeyTab = array_flip($oldTabs);
        foreach ($delTabs as $table) {
            $db->delTable($valToKeyTab[$table]);
        }
        foreach ($insertTabs as $tab) {
            $res = $db->insertTable($tab,$newdb,$id,$prefix);
        }
        $ret['status'] = true;
        return response()->json($ret);
    }
    //更新数据库连接信息
    public function updateDatabaseConfig(Request $request)
    {
        $data = $request->all();
        unset($data['_token']);
        foreach ($data as $key=>$value) {
            if($key == 'prefix') continue;
            if($value == '') unset($data[$key]);
        }
        switch ($data['charset']) {
            case 'utf8':
                $data['collation'] = 'utf8_general_ci';
                break;
            case 'utf8mb4':
                $data['collation'] = 'utf8mb4_general_ci';
                break;
            case 'latin1':
                $data['collation'] = 'gbk_general_ci';
                break;
            case 'gbk':
                $data['collation'] = 'gbk_general_ci';
                break;
        }
        $DataBase_c = new DataBase_c;
        $where['id'] = $data['id'];
        unset($data['id']);
        $DataBase_c->where($where)->update($data);
        return redirect()->back();
    }
    //删除数据库或者数据表
    public function del(Request $request)
    {
        $info  = $request->all();
        $table = $info['table'];
        $id    = $info['id'];
        $db = new DataBase;
        if($table == 'database'){
            $res = $db->delDatabase($id);
        }
        if($table = 'table'){
            $res = $db->delTable($id);
        }
        $ret['status'] = true;
        return response()->json($ret);
    }
}
