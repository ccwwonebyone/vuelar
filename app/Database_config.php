<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Database_config extends Model
{
    //数据表
    protected $table = 'database_config';
    //设置无时间戳字段
    public $timestamps = false;
    /**
     * 插入一条新的数据库配置信息
     * @param array $dbcon  数据库配置信息
     * @return int  插入新的Id
     */
    public function insertInfo($dbcon)
    {
    	$insertId = $this->insertGetId($dbcon);
    	return $insertId;
    }
}
